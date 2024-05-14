<?php

namespace App\Filament\Staff\Resources\SemesterResource\Pages;

use App\Filament\Staff\Resources\SemesterResource;
use Filament\Resources\Pages\EditRecord;

class EditSemester extends EditRecord
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SemesterResource\Widgets\CalendarWidget::class,
        ];
    }
}
