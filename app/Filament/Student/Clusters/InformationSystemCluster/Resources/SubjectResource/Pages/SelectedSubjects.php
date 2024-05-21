<?php

namespace App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource\Pages;

use App\Filament\Student\Clusters\InformationSystemCluster\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class SelectedSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->actions([
                //
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah KRS'),
        ];
    }

    public function getBreadcrumb(): ?string
    {
        return null;
    }
}
