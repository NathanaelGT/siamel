<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\Pages;

use App\Enums\CourseParity;
use App\Enums\WorkingDay;
use App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource;
use App\Models\Semester;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Period\Period;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use WeakMap;

class ListSubject extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected static ?string $breadcrumb = 'Daftar';

    protected static ?string $title = 'KRS';

    public function boot(): void
    {
        Gate::authorize(Period::KRS);
    }

    public function table(Table $table): Table
    {
        $studentSubjects = auth()->user()
            ->student->currentSemesterSubjects()
            ->orderBy('start_time')
            ->with('course:id,name,credits')
            ->get(['course_id', 'parallel', 'code', 'day', 'start_time'])
            ->groupBy('day');

        $overlaps = new WeakMap();

        return $table
            ->paginated(false)
            ->defaultGroup('course_name')
            ->groups([
                Tables\Grouping\Group::make('course_name')
                    ->label('Mata kuliah')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        $query->orderBy('course_name', $direction)
                            ->orderByRaw("field(`subjects`.`day`, ?, ?, ?, ?, ?) $direction", WorkingDay::cases())
                            ->orderBy('subjects.start_time', $direction)
                            ->orderBy('subjects.parallel', $direction);
                    }),

                Tables\Grouping\Group::make('day')
                    ->label('Hari')
                    ->collapsible()
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        $query->orderByRaw("field(`subjects`.`day`, ?, ?, ?, ?, ?) $direction", WorkingDay::cases())
                            ->orderBy('subjects.start_time', $direction)
                            ->orderBy('course_name', $direction)
                            ->orderBy('subjects.parallel', $direction);
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('course_name')
                    ->label('Mata kuliah')
                    ->formatStateUsing(function (Subject $record) {
                        return $record->course_name . ' ' . $record->parallel . $record->code;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('course_credits')
                    ->label('SKS'),

                Tables\Columns\TextColumn::make('day'),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam')
                    ->formatStateUsing(fn(Subject $record) => implode(' - ', [
                        $record->start_time->format('H:i'),
                        $record->start_time->clone()->addMinutes($record->course_credits * 50)->format('H:i'),
                    ])),

                Tables\Columns\TextColumn::make('capacity')
                    ->color(fn(Subject $record) => match (true) {
                        $record->students_count >= $record->capacity       => 'danger',
                        $record->students_count >= $record->capacity * 0.8 => 'warning',
                        default                                            => 'success',
                    })
                    ->weight(fn(Subject $record) => match (true) {
                        $record->students_count >= $record->capacity * 0.8 => FontWeight::Bold,
                        default                                            => FontWeight::Medium,
                    })
                    ->formatStateUsing(function (Subject $record) {
                        return "$record->students_count/$record->capacity";
                    }),
            ])
            ->actions([
                SubjectResource\Actions\RegisterAction::make()
                    ->button()
                    ->disabled(function (Subject $record) use ($studentSubjects, &$overlaps) {
                        if ($record->students_count >= $record->capacity) {
                            return true;
                        }

                        /** @var \Illuminate\Database\Eloquent\Collection<Subject> $subjects */
                        if ($subjects = $studentSubjects->get($record->day->value)) {
                            return $subjects->some(function (Subject $subject) use ($record, &$overlaps) {
                                if ($isOverlapping = $subject->time_period->overlaps($record->time_period)) {
                                    $overlaps[$record] = $subject;
                                }

                                return $isOverlapping;
                            });
                        }

                        return false;
                    })
                    // FIXME: tooltip engga muncul kalo disabled(true)
                    ->tooltip(function (Subject $record) use ($overlaps) {
                        if (isset($overlaps[$record])) {
                            return 'Bentrok dengan ' . $overlaps[$record]->title;
                        }

                        return null;
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->select([
                        'subjects.id',
                        'courses.name as course_name',
                        'subjects.parallel',
                        'subjects.code',
                        'courses.credits as course_credits',
                        'subjects.day',
                        'subjects.start_time',
                        DB::raw('(select count(*) from `student_subject` where `student_subject`.`subject_id` = `subjects`.`id`) as `students_count`'),
                        'subjects.capacity',
                    ])
                    ->join('courses', function (JoinClause $join) {
                        $join->on('courses.id', '=', 'subjects.course_id')
                            ->whereIn('courses.semester_parity', [Semester::current()->parity, CourseParity::Null])
                            ->where('courses.semester_required', '<=', auth()->user()->info->semester)
                            ->where('courses.study_program_id', auth()->user()->info->study_program_id);
                    })
                    ->where('subjects.semester_id', Semester::current()->id)
                    ->whereNotIn(
                        'subjects.course_id',
                        Subject::query()
                            ->select('course_id')
                            ->whereIn(
                                'id',
                                StudentSubject::query()
                                    ->select('subject_id')
                                    ->where('student_id', auth()->user()->info_id)
                            )
                    );
            });
    }
}
