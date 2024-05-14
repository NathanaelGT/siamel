<?php

namespace App\Filament\Staff\Resources\StudyProgramResource\Pages;

use App\Filament\Staff\Resources\StudyProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudyPrograms extends ListRecords
{
    protected static string $resource = StudyProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
