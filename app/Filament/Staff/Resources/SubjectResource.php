<?php

namespace App\Filament\Staff\Resources;

use App\Enums\EmployeeStatus;
use App\Filament\Resource;
use App\Filament\Staff\Resources\SubjectResource\Pages;
use App\Filament\Staff\Resources\SubjectResource\RelationManagers;
use App\Models\Course;
use App\Models\Professor;
use App\Models\Subject;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationGroup = 'Pendidikan';

    protected static ?string $navigationIcon = 'lucide-book-open-text';

    protected static ?int $navigationSort = 7;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereIn('course_id', once(fn() => Course::query()
                            ->where('name', 'like', "%$search%")
                            ->pluck('id')));
                    }),

                Tables\Columns\TextColumn::make('course.studyProgram.name')
                    ->hidden(fn(Component $livewire) => $livewire instanceof RelationManager)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('course.studyProgram.faculty.name')
                    ->hidden(function (Component $livewire) {
                        if ($livewire instanceof RelationManager) {
                            return true;
                        }

                        return auth()->user()->info->faculty_id !== null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('professor.account.name')
                    ->label('Dosen')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('room.full_name')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->hidden(fn(Component $livewire) => $livewire instanceof RelationManager)
                    ->label('Kapasitas')
                    ->formatStateUsing(function (Subject $record) {
                        return "$record->students_count/$record->capacity";
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('day')
                    ->sortable(),

                Tables\Columns\TextColumn::make('time'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),
            ])
            ->modifyQueryUsing(function (Component $livewire, Builder $query) {
                if ($livewire instanceof RelationManager) {
                    return;
                }

                $query->withCount('students');
            });
    }

    public static function getEloquentQuery(): Builder
    {
        return Subject::query()
            ->with(['room.building'])
            ->when(auth()->user()->info->faculty_id, function (Builder $query, int $facultyId) {
                $query->whereIn('professor_id', once(fn() => Professor::query()
                    ->where('status', EmployeeStatus::Active)
                    ->where('faculty_id', $facultyId)
                    ->pluck('id')
                    ->all()));
            });
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\GroupsRelationManager::class,
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
