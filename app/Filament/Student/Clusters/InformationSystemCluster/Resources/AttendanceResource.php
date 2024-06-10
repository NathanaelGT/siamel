<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources;

use App\Enums\AttendanceStatus;
use App\Filament\Resource;
use App\Filament\Student\Clusters\InformationSystemCluster;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\AttendanceResource\Pages;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\AttendanceResource\RelationManagers;
use App\Filament\Tables\Summarizer\LocalAverage;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\SubjectSchedule;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;
use stdClass;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'lucide-notebook-tabs';

    protected static ?string $cluster = InformationSystemCluster::class;

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->numeric()
                    ->default(fn(stdClass $rowLoop) => $rowLoop->iteration),

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
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return Subject::query()
            ->with([
                'schedules'             => function (HasMany $query) {
                    $query->where('start_time', '<', now())
                        ->select(['id', 'subject_id']);
                },
                'schedules.attendances' => function (HasMany $query) {
                    $query->where('student_id', auth()->user()->info_id)
                        ->select(['subject_schedule_id', 'status']);
                },
            ])
            ->whereIn('id', StudentSubject::query()
                ->where('student_id', auth()->user()->info_id)
                ->select('subject_id'))
            ->where('semester_id', Semester::current()->id);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
        ];
    }
}
