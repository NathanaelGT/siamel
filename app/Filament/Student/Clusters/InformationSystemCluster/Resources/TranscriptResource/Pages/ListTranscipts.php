<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource\Pages;

use App\Filament\Student\Clusters\InformationSystemCluster\Resources\TranscriptResource;
use Filament\Resources\Pages\ListRecords;

class ListTranscipts extends ListRecords
{
    protected static string $resource = TranscriptResource::class;

    public function getTitle(): string
    {
        return 'Transkrip ' . auth()->user()->info_id . ' - ' . auth()->user()->name;
    }

    protected function getHeaderWidgets(): array
    {
        return TranscriptResource::getWidgets();
    }

    public function getBreadcrumb(): ?string
    {
        return null;
    }
}
