<?php

namespace App\Filament\Staff\Resources\StudyProgramResource\Pages;

use App\Filament\Staff\Resources\StudyProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudyProgram extends ViewRecord
{
    protected static string $resource = StudyProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
