<?php

namespace App\Filament\Staff\Resources\CourseResource\RelationManagers;

use App\Enums\EmployeeStatus;
use App\Filament\RelationManager;
use App\Models\Course;
use Filament\Tables;
use Filament\Tables\Table;

/** @property-read Course $ownerRecord */
class ProfessorsRelationManager extends RelationManager
{
    protected static string $relationship = 'professors';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account.name')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('id'),

                Tables\Columns\TextColumn::make('account.name'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(EmployeeStatus::badgeColor(...)),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Hilangkan'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hilangkan yang dipilih'),
            ]);
    }
}
