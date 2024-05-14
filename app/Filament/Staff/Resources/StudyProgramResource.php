<?php

namespace App\Filament\Staff\Resources;

use App\Enums\EducationLevel;
use App\Enums\Role;
use App\Enums\StudentStatus;
use App\Filament\Resource;
use App\Filament\Staff\Resources\StudyProgramResource\Pages;
use App\Filament\Staff\Resources\StudyProgramResource\RelationManagers;
use App\Models\StudyProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudyProgramResource extends Resource
{
    protected static ?string $model = StudyProgram::class;

    protected static ?string $navigationGroup = 'Fasilitas';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(fn(Component $livewire) => array_filter([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                $livewire instanceof RelationManager ? null :
                    Forms\Components\Select::make('faculty_id')
                        ->hidden(fn() => Auth::user()->info->faculty_id !== null)
                        ->relationship('faculty', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                Forms\Components\Select::make('level')
                    ->options(EducationLevel::class)
                    ->searchable()
                    ->required(),
            ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('faculty.name')
                    ->searchable()
                    ->hidden(function (Component $livewire) {
                        return $livewire instanceof RelationManager ||
                            Auth::user()->role !== Role::Admin;
                    }),

                Tables\Columns\TextColumn::make('level'),

                Tables\Columns\TextColumn::make('courses_count')
                    ->label('Mata kuliah'),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Mahasiswa aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount([
                    'courses',
                    'students' => function (Builder $query) {
                        $query->where('status', StudentStatus::Active);
                    },
                ]);
            });
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
            RelationManagers\CoursesRelationManager::class,
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudyPrograms::route('/'),
            'create' => Pages\CreateStudyProgram::route('/baru'),
            'view'   => Pages\ViewStudyProgram::route('/{record}'),
            'edit'   => Pages\EditStudyProgram::route('/{record}/edit'),
        ];
    }
}
