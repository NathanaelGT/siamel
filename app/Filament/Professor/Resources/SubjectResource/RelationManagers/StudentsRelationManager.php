<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Enums\AttendanceStatus;
use App\Filament\RelationManager;
use App\Models\Attendance;
use App\Models\Student;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;

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
                    ->titlePrefixedWithLabel(false),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('id'),

                Tables\Columns\TextColumn::make('account.name'),

                Tables\Columns\TextColumn::make('attendances')
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
                    ->visible($hasGroups),
            ])
            ->modifyQueryUsing(function (Builder $query) use ($hasGroups) {
                $query->with([
                    'attendances' => function (HasMany $query) {
                        $query->select(['student_id', 'status'])->whereIn(
                            'subject_schedule_id',
                            $this->ownerRecord->schedules()->select('id')
                        );
                    },
                ]);

                if ($hasGroups) {
                    $query->addSelect('subject_groups.name as group_name')
                        ->leftJoin('subject_group_members', function (JoinClause $query) {
                            $query->on('students.id', '=', 'subject_group_members.student_id')
                                ->whereNull('subject_group_members.deleted_at');
                        })
                        ->leftJoin('subject_groups', function (JoinClause $query) {
                            $query->on('subject_group_members.subject_group_id', '=', 'subject_groups.id')
                                ->whereNull('subject_groups.deleted_at');
                        });
                }
            });
    }
}
