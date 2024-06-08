<?php

namespace App\Filament\Staff\Resources\SubjectResource\Pages;

use App\Filament\Staff\Resources\SubjectResource;
use App\Models\Semester;
use App\Period\Period;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->authorize(Gate::check(Period::KRSPreparation)),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->where('semester_id', Semester::current()->id);
    }
}
