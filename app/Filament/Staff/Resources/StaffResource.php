<?php

namespace App\Filament\Staff\Resources;

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Filament\Resource;
use App\Filament\Staff\Resources\StaffResource\Pages;
use App\Filament\Staff\Resources\StaffResource\RelationManagers;
use App\Filament\Tables\Columns\AvatarColumn;
use App\Models\Faculty;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationGroup = 'Pengguna';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('account.name')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('account.gender')
                ->options(Gender::class)
                ->searchable()
                ->required(),

            Forms\Components\Select::make('faculty_id')
                ->disabledOn('edit')
                ->relationship('faculty', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('id')
                ->disabledOn('edit')
                ->unique()
                ->required()
                ->integer()
                ->maxLength(11)
                ->extraInputAttributes([
                    'class' => 'input-no-arrow',
                ]),

            Forms\Components\TextInput::make('account.email')
                ->disabledOn('edit')
                ->email()
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('account.phone_number')
                ->tel()
                ->required()
                ->maxLength(16),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                AvatarColumn::make('account.avatar_url')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('account.phone_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('account.gender')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('faculty.name')
                    ->when(
                        fn() => Auth::user()->info->faculty_id !== null,
                        fn(Tables\Columns\TextColumn $column) => $column->hidden(),
                        fn(Tables\Columns\TextColumn $column) => $column->toggleable(isToggledHiddenByDefault: true),
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(EmployeeStatus::badgeColor(...))
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('faculty_id')
                    ->hidden(fn() => Auth::user()->info->faculty_id !== null)
                    ->options(function () {
                        return Faculty::query()
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->indicateUsing(function (Tables\Filters\SelectFilter $filter, array $state) {
                        if (blank($state['values'] ?? null)) {
                            return [];
                        }

                        $labels = Arr::only($filter->getOptions(), $state['values']);

                        if (empty($labels)) {
                            return [];
                        }

                        $labels = collect($labels)
                            ->map(abbreviation(...))
                            ->join(', ', ' & ');

                        $indicator = $filter->getIndicator();

                        if (! $indicator instanceof Indicator) {
                            $indicator = Indicator::make("{$indicator}: {$labels}");
                        }

                        return [$indicator];
                    })
                    ->searchable()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(EmployeeStatus::class)
                    ->default([EmployeeStatus::Active->value])
                    ->searchable()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $facultyId = Auth::user()->info->faculty_id;

        return parent::getEloquentQuery()
            ->when($facultyId !== null)->where('faculty_id', $facultyId);
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
            'index'  => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/baru'),
            'view'   => Pages\ViewStaff::route('/{record}'),
            'edit'   => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
