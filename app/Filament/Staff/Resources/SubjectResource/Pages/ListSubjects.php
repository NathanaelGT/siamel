<?php

namespace App\Filament\Staff\Resources\SubjectResource\Pages;

use App\Filament\Staff\Resources\SubjectResource;
use App\Models\Semester;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ->where('semester_id', Semester::query()->current()->select('id'));
    }
}
