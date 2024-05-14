<?php

namespace App\Filament\Staff\Resources\FacultyResource\Pages;

use App\Filament\Staff\Resources\FacultyResource;
use Filament\Resources\Pages\EditRecord;

class EditFaculty extends EditRecord
{
    protected static string $resource = FacultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
