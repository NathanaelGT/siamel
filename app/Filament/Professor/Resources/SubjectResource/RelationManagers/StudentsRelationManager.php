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
use stdClass;

/** @property-read \App\Models\Subject $ownerRecord */
class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordTitleAttribute('account.name')
            ->columns([
                Tables\Columns\TextColumn::make('absence')
                    ->label('Absen')
                    ->default(fn(stdClass $rowLoop) => $rowLoop->iteration)
                    ->numeric(),

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
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['attendances' => function (HasMany $query) {
                    $query->select(['student_id', 'status'])->whereIn(
                        'subject_schedule_id',
                        $this->ownerRecord->schedules()->select('id')
                    );
                }]);
            });
    }
}
