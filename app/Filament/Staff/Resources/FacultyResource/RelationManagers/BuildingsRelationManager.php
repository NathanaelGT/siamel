<?php

namespace App\Filament\Staff\Resources\FacultyResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Staff\Resources\BuildingResource;
use App\Models\Building;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class BuildingsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama gedung')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Building $record) => BuildingResource::getUrl('view', [$record])),

                Tables\Actions\EditAction::make(),
            ]);
    }
}
