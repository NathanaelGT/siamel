<?php

namespace App\Filament\Staff\Resources\FacultyResource\Pages;

use App\Filament\Staff\Resources\FacultyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFaculty extends ViewRecord
{
    protected static string $resource = FacultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
