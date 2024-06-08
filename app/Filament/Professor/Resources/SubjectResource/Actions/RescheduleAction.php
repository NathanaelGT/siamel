<?php

namespace App\Filament\Professor\Resources\SubjectResource\Actions;

use App\Models\Subject;
use App\Models\SubjectSchedule;
use App\Period\Period;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class RescheduleAction extends Action
{
    protected Subject | Closure $subject;

    public function subject(Subject | Closure $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public static function getDefaultName(): ?string
    {
        return 'reschedule';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-pencil-square');
        $this->disabled(fn(SubjectSchedule $record) => $record->start_time->clone()->addWeek()->isPast());
        $this->modalHeading(fn(SubjectSchedule $record) => 'Atur ulang jadwal pertamuan ke ' . $record->meeting_no);
        $this->successNotificationTitle(fn(SubjectSchedule $record) => 'Jadwal pertemuan ke ' . $record->meeting_no . ' berhasil diatur ulang');
        $this->modalSubmitActionLabel('Reschedule');
        $this->authorize(Gate::check(Period::Learning));

        $this->form(fn(Form $form, SubjectSchedule $record) => $form->columns(2)->schema([
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
        ]));

        $this->action(function (SubjectSchedule $record, array $data) {
            $subject = $this->evaluate($this->subject);

            $record->update([
                'start_time' => $data['start_time'],
                'end_time'   => Carbon::create($data['start_time'])->addMinutes((int) $data['duration']),
            ]);

            $subject->notifyStudents(
                Notification::make()
                    ->title('Reschedule Jadwal Pertemuan')
                    ->icon('heroicon-o-clock')
                    ->info()
                    ->body(
                        "Waktu pembelajaran ke-{$record->meeting_no} pada kelas {$subject->course->name} " .
                        "telah ganti menjadi {$record->start_time->translatedFormat('l, j F')} " .
                        "pukul {$record->start_time->format('H:i')} - {$record->end_time->format('H:i')}."
                    )
            );

            $this->success();
        });
    }
}
