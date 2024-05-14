<?php

namespace App\Filament\Staff\Resources\FacultyResource\Pages;

use App\Filament\Staff\Resources\FacultyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFaculties extends ListRecords
{
    protected static string $resource = FacultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
