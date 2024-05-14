<?php

namespace App\Filament\Staff\Resources;

use App\Enums\EmployeeStatus;
use App\Filament\Resource;
use App\Filament\Staff\Resources\SubjectResource\Pages;
use App\Filament\Staff\Resources\SubjectResource\RelationManagers;
use App\Models\Professor;
use App\Models\Subject;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationGroup = 'Pendidikan';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('professor.account.name')
                    ->label('Dosen')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('room.name')
                    ->sortable()
                    ->formatStateUsing(static fn(Subject $record) => implode(' ', [
                        $record->room->building->abbreviation,
                        $record->room->name,
                    ]))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('parallel')
                    ->formatStateUsing(static fn(Subject $record) => implode([
                        $record->parallel,
                        $record->code,
                    ])),

                Tables\Columns\TextColumn::make('day')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam')
                    ->formatStateUsing(static fn(Subject $record) => implode(' - ', [
                        $record->start_time->format('H:i'),
                        $record->end_time->format('H:i'),
                    ])),
            ])
            ->filters([
                //
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
        $professorIds = once(function () {
            $facultyId = Auth::user()->info->faculty_id;

            return Professor::query()
                ->where('status', EmployeeStatus::Active)
                ->when($facultyId !== null)->where('faculty_id', $facultyId)
                ->pluck('id')
                ->all();
        });

        return Subject::query()
            ->with(['room.building'])
            ->whereIn('professor_id', $professorIds);
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
            'index'  => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/baru'),
            'view'   => Pages\ViewSubject::route('/{record}'),
            'edit'   => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
