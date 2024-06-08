<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Filament\Professor\Resources\SubjectResource;
use App\Filament\RelationManager;
use App\Filament\Student;
use App\Models\SubjectSchedule;
use App\Period\Period;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                SubjectResource\Actions\RescheduleAction::make()
                    ->subject($this->ownerRecord),

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
