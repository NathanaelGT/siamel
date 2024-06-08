<?php

namespace App\Filament\Staff\Resources\StudentResource\RelationManagers;

use App\Enums\AttendanceStatus;
use App\Filament\RelationManager;
use App\Filament\Tables\Summarizer\LocalAverage;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\SubjectSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

/** @property-read \App\Models\Student $ownerRecord */
class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->paginated(false)
            ->defaultGroup(Tables\Grouping\Group::make('semester_id')
                ->collapsible()
                ->titlePrefixedWithLabel(false)
                ->getTitleFromRecordUsing(function (Subject $record) {
                    return $record->semester->academic_year;
                }))
            ->columns([
                Tables\Columns\TextColumn::make('course.name'),

                Tables\Columns\TextColumn::make('parallel')
                    ->label('Kelas')
                    ->formatStateUsing(function (Subject $record) {
                        return $record->parallel . $record->code;
                    }),

                Tables\Columns\TextColumn::make('attendance_rate')
                    ->label('Kehadiran')
                    ->summarize([
                        $avgAttendance = LocalAverage::make()
                            ->formatStateUsing(function (float $state) {
                                $attendance = round($state);

                                if ($attendance >= 80) {
                                    return $attendance . '%';
                                }

                                $rgb = Color::Red[600];

                                return new HtmlString(
                                    "<span style=\"color:rgb($rgb)\">$attendance%</span>"
                                );
                            }),
                    ])
                    ->default(function (Subject $record) use ($avgAttendance) {
                        if (isset($record->attendance_rate)) {
                            return $record->attendance_rate;
                        }

                        $attendance = $record->schedules->percentage(function (SubjectSchedule $schedule) {
                            return ! in_array($schedule->attendances->first()?->status, [
                                null,
                                AttendanceStatus::Absent,
                            ]);
                        });

                        $avgAttendance->increaseValue($attendance);

                        return $record->attendance_rate = (int) round($attendance);
                    })
                    ->formatStateUsing(fn(int $state) => "$state%")
                    ->color(function (int $state) {
                        if ($state < 80) {
                            return 'danger';
                        }

                        return null;
                    }),
            ])
            ->query(function () {
                return Subject::query()
                    ->with([
                        'schedules'             => function (HasMany $query) {
                            $query->where('start_time', '<', now())
                                ->select(['id', 'subject_id']);
                        },
                        'schedules.attendances' => function (HasMany $query) {
                            $query->where('student_id', $this->ownerRecord->id)
                                ->select(['subject_schedule_id', 'status']);
                        },
                        'semester',
                    ])
                    ->whereIn('id', StudentSubject::query()
                        ->where('student_id', $this->ownerRecord->id)
                        ->select('subject_id'));
            });
    }
}
