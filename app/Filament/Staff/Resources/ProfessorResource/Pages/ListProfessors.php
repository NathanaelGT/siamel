<?php

namespace App\Filament\Staff\Resources\ProfessorResource\Pages;

use App\Filament\Staff\Resources\ProfessorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProfessors extends ListRecords
{
    protected static string $resource = ProfessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
