<?php

namespace App\Filament\Staff\Resources\StudentResource\RelationManagers;

use App\Enums\WorkingDay;
use App\Filament\RelationManager;
use App\Filament\Staff\Resources\SubjectResource;
use App\Models\Semester;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/** @property-read \App\Models\Student $ownerRecord */
class ScheduleRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';

    protected static ?string $title = 'Jadwal';

    public function table(Table $table): Table
    {
        return SubjectResource::table($table)
            ->recordTitleAttribute('course.name')
            ->paginated(false)
            ->defaultSort(function (Builder $query) {
                $query->orderByRaw('field(`subjects`.`day`, ?, ?, ?, ?, ?) asc', WorkingDay::cases())
                    ->orderBy('subjects.start_time');
            })
            ->filters([
                Tables\Filters\SelectFilter::make('semester_id')
                    ->native(false)
                    ->options(fn() => $this->ownerRecord->semester_labels)
                    ->default(function (Tables\Filters\SelectFilter $selectFilter) {
                        $options = $selectFilter->getOptions();

                        if (isset($options[Semester::current()->id])) {
                            return Semester::current()->id;
                        }

                        return Arr::firstKey($options);
                    }),
            ]);
    }
}
