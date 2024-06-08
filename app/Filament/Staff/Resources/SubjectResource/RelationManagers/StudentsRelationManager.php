<?php

namespace App\Filament\Staff\Resources\SubjectResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Staff\Resources\StudentResource;
use App\Filament\Tables\Columns\AvatarColumn;
use App\Models\Student;
use App\Period\Period;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use stdClass;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Mahasiswa';

    protected static bool $shouldSkipAuthorization = true;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('account.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account.name')
            ->paginated(false)
            ->defaultSort('id')
            ->columns([
                AvatarColumn::make('account.avatar_url')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('absence')
                    ->label('Absen')
                    ->default(fn(stdClass $rowLoop) => $rowLoop->iteration)
                    ->numeric()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('registered_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Student $student) => StudentResource::getUrl('view', [$student])),

                Tables\Actions\DetachAction::make()
                    ->label('Keluarkan')
                    ->authorize(Gate::check(Period::KRSPreparation)),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Keluarkan yang dipilih')
                    ->authorize(Gate::check(Period::KRSPreparation)),
            ]);
    }
}
