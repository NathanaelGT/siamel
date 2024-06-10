<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\AttendanceResource\Pages;

use App\Filament\Student\Clusters\InformationSystemCluster\Resources\AttendanceResource;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getBreadcrumb(): ?string
    {
        return null;
    }
}
