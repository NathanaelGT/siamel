<?php

namespace App\Filament\Staff\Resources;

use App\Enums\Parity;
use App\Filament\Resource;
use App\Filament\Staff\Resources\SemesterResource\Pages;
use App\Filament\Staff\Resources\SemesterResource\RelationManagers;
use App\Models\Semester;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parity')
                    ->options(Parity::class)
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('year')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('academic_year', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('academic_year')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SemesterResource\Widgets\CalendarWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSemesters::route('/'),
            'create' => Pages\CreateSemester::route('/create'),
            'edit'   => Pages\EditSemester::route('/{record}/edit'),
        ];
    }
}
