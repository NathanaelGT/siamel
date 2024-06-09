<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Enums\AttendanceStatus;
use App\Filament\RelationManager;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\SubjectGroup;
use App\Period\Period;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** @property-read \App\Models\Subject $ownerRecord */
class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function table(Table $table): Table
    {
        $hasGroups = $this->ownerRecord->groups()->exists();

        return $table
            ->paginated(false)
            ->recordTitleAttribute('account.name')
            ->groups(array_filter([
                ! $hasGroups ? null : Tables\Grouping\Group::make('group_name')
                    ->label('Kelompok')
                    ->titlePrefixedWithLabel(false)
                    ->orderQueryUsing(function (Builder $query, string $direction) {
                        $query
                            ->orderByRaw("if(`group_name` like 'kelompok %', cast(substring(`group_name`, 10) as unsigned), 99999) $direction")
                            ->orderBy('group_name', $direction);
                    }),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('id'),

                Tables\Columns\TextColumn::make('account.name'),

                Tables\Columns\TextColumn::make('attendances')
                    ->visible(Gate::check(Period::Learning))
                    ->formatStateUsing(function (Student $record) {
                        $record->attendance = round($record->attendances->percentage(function (Attendance $attendance) {
                            return ! in_array($attendance->status, [
                                null,
                                AttendanceStatus::Absent,
                            ]);
                        }));

                        return $record->attendance . '%';
                    })
                    ->color(function (Student $record) {
                        if ($record->attendance < 80) {
                            return 'danger';
                        }

                        return null;
                    }),

                Tables\Columns\TextColumn::make('group_name')
                    ->label('Kelompok')
                    ->placeholder('Belum memiliki kelompok')
                    ->visible($hasGroups && Gate::check(Period::Learning)),
            ])
            ->modifyQueryUsing(function (Builder $query) use ($hasGroups) {
                if (! Gate::check(Period::Learning)) {
                    return;
                }

                $query->with([
                    'attendances' => function (HasMany $query) {
                        $query->select(['student_id', 'status'])->whereIn(
                            'subject_schedule_id',
                            $this->ownerRecord->schedules()->select('id')
                        );
                    },
                ]);

                if (! $hasGroups) {
                    return;
                }

                $query->addSelect(DB::raw(
                    '(' .
                    SubjectGroup::query()
                        ->select('subject_groups.name')
                        ->join('subject_group_members', function (JoinClause $query) {
                            $query->on('subject_groups.id', '=', 'subject_group_members.subject_group_id')
                                ->whereColumn('subject_group_members.student_id', 'students.id')
                                ->whereNull('subject_group_members.deleted_at');
                        })
                        ->whereColumn('subject_groups.subject_id', 'student_subject.subject_id')
                        ->toRawSql() .
                    ') as `group_name`'
                ));
            });
    }
}
