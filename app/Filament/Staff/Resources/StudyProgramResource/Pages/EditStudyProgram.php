<?php

namespace App\Filament\Staff\Resources\StudyProgramResource\Pages;

use App\Filament\Staff\Resources\StudyProgramResource;
use Filament\Resources\Pages\EditRecord;

class EditStudyProgram extends EditRecord
{
    protected static string $resource = StudyProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
