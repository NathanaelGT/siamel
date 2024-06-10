<?php

namespace App\Filament\Staff\Resources;

use App\Enums\Parity;
use App\Filament\Resource;
use App\Filament\Staff\Resources\SemesterResource\Pages;
use App\Filament\Staff\Resources\SemesterResource\RelationManagers;
use App\Models\Semester;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;

    protected static ?string $navigationIcon = 'lucide-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('academic_year', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('academic_year')
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy('year', $direction)
                            ->orderByRaw("field(`parity`, ?, ?) $direction", Parity::cases());
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSemesters::route('/'),
        ];
    }
}
