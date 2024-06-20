<?php

namespace App\Filament\Professor\Resources\SubjectResource\Pages;

use App\Enums\CourseParity;
use App\Enums\WorkingDay;
use App\Filament\Professor\Resources\SubjectResource;
use App\Models\Semester;
use App\Models\StudentSubject;
use App\Models\StudyProgram;
use App\Models\Subject;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    public function table(Table $table): Table
    {
        $semesters = Semester::query()
            ->orderBy('id', 'desc')
            ->limit(8)
            ->pluck('academic_year', 'id')
            ->map(fn(string $label) => substr($label, 9))
            ->all();

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
                Tables\Columns\TextColumn::make('title')
                    ->label('Mata kuliah')
                    ->searchable('courses.name'),

                Tables\Columns\TextColumn::make('room_full_name')
                    ->label('Ruangan'),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Kapasitas')
                    ->formatStateUsing(function (Subject $record) {
                        return "$record->students_count/$record->capacity";
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('day'),

                Tables\Columns\TextColumn::make('time'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('semester_id')
                    ->native(false)
                    ->default(Arr::firstKey($semesters))
                    ->options($semesters),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->select([
                        'subjects.id',
                        'courses.name as course_name',
                        'subjects.parallel',
                        'subjects.code',
                        DB::raw('concat(`buildings`.`abbreviation`, \' \', `rooms`.`name`) as `room_full_name`'),
                        'courses.credits as course_credits',
                        'subjects.day',
                        'subjects.start_time',
                        DB::raw('(select count(*) from `student_subject` where `student_subject`.`subject_id` = `subjects`.`id`) as `students_count`'),
                        'subjects.capacity',
                        'subjects.slug',
                    ])
                    ->join('courses', function (JoinClause $join) {
                        $join->on('courses.id', '=', 'subjects.course_id')
                            ->whereIn('courses.semester_parity', [Semester::current()->parity, CourseParity::Null])
                            ->whereIn(
                                'courses.study_program_id',
                                StudyProgram::query()
                                    ->select('id')
                                    ->where('faculty_id', auth()->user()->info->faculty_id)
                            );
                    })
                    ->join('rooms', 'rooms.id', '=', 'subjects.room_id')
                    ->join('buildings', 'buildings.id', '=', 'rooms.building_id')
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
