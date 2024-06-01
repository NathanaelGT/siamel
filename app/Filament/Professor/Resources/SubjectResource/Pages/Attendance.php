<?php

namespace App\Filament\Professor\Resources\SubjectResource\Pages;

use App\Enums\AttendanceStatus;
use App\Filament\Professor\Resources\SubjectResource;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectSchedule;
use App\Providers\FilamentServiceProvider;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

/**
 * @property-read SubjectSchedule $subjectSchedule
 * @property-read Subject $subject
 */
class Attendance extends ListRecords
{
    #[Locked]
    public string $record;

    #[Locked]
    public string $meetingNo;

    protected static string $resource = SubjectResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('attendances.status')
                    ->placeholder('Belum diabsen')
                    ->badge()
                    ->tooltip(function (Student $record) {
                        $date = $record
                            ->attendances
                            ->first()
                            ?->date
                            ->format(FilamentServiceProvider::DEFAULT_DATE_TIME_DISPLAY_FORMAT);

                        if ($date) {
                            return "Terakhir diubah pada $date";
                        }

                        return null;
                    }),
            ])
            ->actions(Arr::map(AttendanceStatus::cases(), function (AttendanceStatus $status) {
                return Tables\Actions\Action::make($status->name)
                    ->label($status->getLabel())
                    ->color($status->getColor())
                    ->button()
                    ->outlined()
                    ->disabled(fn(Student $record) => $record->attendances->first()?->status === $status)
                    ->requiresConfirmation(fn(Student $record) => $record->attendances->isNotEmpty())
                    ->modalDescription(fn(Student $record) => $record->attendances->isNotEmpty()
                        ? "Apakah anda ingin mengganti status absensi {$record->account->name} menjadi {$status->getLabel()}"
                        : null)
                    ->successNotificationTitle(function (Student $record) use ($status) {
                        return "Berhasil mengubah status absen {$record->account->name} menjadi {$status->getLabel()}";
                    })
                    ->failureNotificationTitle(function (Student $record) {
                        return "Anda tidak mempunyai akses untuk mengubah absensi {$record->account->name} pada kelas {$this->subject->title}";
                    })
                    ->action(function (Student $record, Tables\Actions\Action $action) use ($status) {
                        $attendance = $record->attendances->first() ?? $record->attendances()->make([
                            'subject_schedule_id' => $this->subjectSchedule->id,
                        ]);

                        $createOrUpdate = $attendance->exists ? 'update' : 'create';

                        if (auth()->user()->cannot($createOrUpdate, $attendance)) {
                            $action->sendFailureNotification();

                            return;
                        }

                        $attendance->fill(['status' => $status])->save();

                        $action->sendSuccessNotification();
                    });
            }));
    }

    protected function getTableQuery(): ?Builder
    {
        return Student::query()
            ->with(['attendances' => function (HasMany $query) {
                $query->where('subject_schedule_id', $this->subjectSchedule->id);
            }])
            ->join('student_subject', function (JoinClause $join) {
                $join->on('students.id', 'student_subject.student_id')
                    ->where('student_subject.subject_id', $this->subject->id);
            });
    }

    public function getTitle(): string
    {
        return 'Absensi Pertemuan Ke ' . $this->meetingNo;
    }

    public function getBreadcrumbs(): array
    {
        $url = fn(string $name = 'index', array $parameters = []) => SubjectResource::getUrl($name, $parameters);

        $breadcrumbs = [];
        $breadcrumbs[$url()] = SubjectResource::getBreadcrumb();
        $breadcrumbs[$viewUrl = $url('view', [$this->subject])] = implode(' - ', [
            $this->subject->course->name . ' ' . $this->subject->parallel . $this->subject->code,
            $this->subject->semester->academic_year,
        ]);
        $breadcrumbs["$viewUrl?activeRelationManager=1"] = SubjectResource\RelationManagers\SchedulesRelationManager::getTitle(
            $this->subject,
            SubjectResource::getPages()['view']->getPage()
        );
        $breadcrumbs[] = 'Pertemuan Ke ' . $this->meetingNo;

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getModel(): string
    {
        return Student::class;
    }

    #[Computed]
    protected function subject(): Subject
    {
        return Subject::query()
            ->where('slug', $this->record)
            ->firstOrFail();
    }

    #[Computed]
    protected function subjectSchedule(): SubjectSchedule
    {
        return $this->subject
            ->schedules()
            ->where('meeting_no', $this->meetingNo)
            ->firstOrFail();
    }
}
