<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Filament\Professor\Resources\SubjectResource;
use App\Filament\RelationManager;
use App\Filament\Student;
use App\Models\SubjectSchedule;
use App\Period\Period;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;

/** @property-read \App\Models\Subject $ownerRecord */
class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn(SubjectSchedule $record) => 'Pertemuan ke ' . $record->meeting_no)
            ->paginated(false)
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('meeting_no')
                        ->formatStateUsing(function (SubjectSchedule $record) {
                            $text = "Pertemuan ke $record->meeting_no";

                            if ($record->start_time->isFuture()) {
                                return new HtmlString('
                                <div
                                    title="Pertemuan ini belum dimulai"
                                    class="fi-in-placeholder text-sm leading-6 text-gray-400 dark:text-gray-500"
                                >
                                    ' . $text . '
                                </div>
                            ');
                            }

                            return $text;
                        }),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('subject:id,slug,course_id');
            })
            ->actions([
                Tables\Actions\Action::make('reschedule')
                    ->icon('heroicon-o-pencil-square')
                    ->disabled(fn(SubjectSchedule $record) => $record->start_time->clone()->addWeek()->isPast())
                    ->modalHeading(fn(SubjectSchedule $record) => 'Atur ulang jadwal pertamuan ke ' . $record->meeting_no)
                    ->successNotificationTitle(fn(SubjectSchedule $record) => 'Jadwal pertemuan ke ' . $record->meeting_no . ' berhasil diatur ulang')
                    ->modalSubmitActionLabel('Reschedule')
                    ->authorize(Gate::check(Period::Learning))
                    ->form(fn(Form $form, SubjectSchedule $record) => $form->columns(2)->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('Waktu mulai')
                            ->native(false)
                            ->seconds(false)
                            ->minutesStep(10)
                            ->default($record->start_time)
                            ->minDate($record->start_time->clone()->startOfWeek())
                            ->maxDate($record->start_time->clone()->endOfWeek())
                            ->required(),

                        Forms\Components\TextInput::make('duration')
                            ->label('Durasi')
                            ->suffix('menit')
                            ->numeric()
                            ->default($default = $record->subject->course->credits * 50)
                            ->minValue(1)
                            ->maxValue($default)
                            ->required(),
                    ]))
                    ->action(function (SubjectSchedule $record, array $data, Tables\Actions\Action $action) {
                        $record->update([
                            'start_time' => $data['start_time'],
                            'end_time'   => Carbon::create($data['start_time'])->addMinutes((int) $data['duration']),
                        ]);

                        $this->ownerRecord->notifyStudents(
                            Notification::make()
                                ->title('Reschedule Jadwal Pertemuan')
                                ->icon('heroicon-o-clock')
                                ->info()
                                ->body(
                                    "Waktu pembelajaran ke-{$record->meeting_no} pada kelas {$this->ownerRecord->course->name} " .
                                    "telah ganti menjadi {$record->start_time->translatedFormat('l, j F')} " .
                                    "pukul {$record->start_time->format('H:i')} - {$record->end_time->format('H:i')}."
                                )
                        );

                        $action->success();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('Absen')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->disabled(fn(SubjectSchedule $record) => $record->start_time->isFuture())
                    ->authorize(Gate::check(Period::Learning))
                    ->url(fn(SubjectSchedule $record) => SubjectResource::getUrl('attendance', [
                        $record->subject,
                        $record->meeting_no,
                    ])),
            ])
            ->recordUrl(function (SubjectSchedule $record) {
                if ($record->start_time->isFuture() || ! Gate::check(Period::Learning)) {
                    return null;
                }

                return SubjectResource::getUrl('attendance', [
                    $record->subject,
                    $record->meeting_no,
                ]);
            });
    }
}
