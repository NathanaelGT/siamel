<?php

namespace App\Filament\Staff\Resources;

use App\Enums\CourseParity;
use App\Filament\Resource;
use App\Filament\Staff\Resources\CourseResource\Pages;
use App\Filament\Staff\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Models\StudyProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationGroup = 'Pendidikan';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $creditInput = Forms\Components\TextInput::make('credits')
            ->required()
            ->integer()
            ->default(3)
            ->minValue(2)
            ->maxValue(6);

        return $form
            ->schema(fn(Component $livewire) => array_filter([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                $livewire instanceof RelationManager ? null :
                    Forms\Components\Select::make('study_program_id')
                        ->relationship('studyProgram', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                $livewire instanceof RelationManager ? $creditInput : null,

                Forms\Components\TextInput::make('semester_required')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(7)
                    ->reactive()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                        if (blank($state) || $get('semester_parity') === CourseParity::Null->value) {
                            return;
                        }

                        $parity = $state % 2 === 0
                            ? CourseParity::Even
                            : CourseParity::Odd;

                        $set('semester_parity', $parity->value);
                    }),

                Forms\Components\Select::make('semester_parity')
                    ->required()
                    ->options(CourseParity::class)
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                        $parity = CourseParity::tryFrom($state);
                        if ($parity === null || $parity === CourseParity::Null) {
                            return;
                        }
                        if (blank($semesterRequired = $get('semester_required'))) {
                            return;
                        }

                        $invalid = match ($parity) {
                            CourseParity::Even => $semesterRequired % 2 === 1,
                            CourseParity::Odd  => $semesterRequired % 2 === 0,
                        };

                        if ($invalid) {
                            $set('semester_required', null);
                        }
                    }),

                $livewire instanceof RelationManager ? null : $creditInput,

                Forms\Components\Section::make()->schema([
                    Forms\Components\Toggle::make('is_elective')
                        ->required(),
                ]),
            ]));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('studyProgram.name')
                    ->hidden(fn(Component $livewire) => $livewire instanceof RelationManager)
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester_required')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester_parity')
                    ->searchable(),

                Tables\Columns\TextColumn::make('credits')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_elective')
                    ->badge()
                    ->formatStateUsing(fn(bool $state) => $state ? 'Ya' : 'Tidak'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
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
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $facultyId = Auth::user()->info->faculty_id;

        return parent::getEloquentQuery()
            ->when($facultyId !== null, function (Builder $query) use ($facultyId) {
                $query->whereIn(
                    'study_program_id',
                    StudyProgram::query()->where('faculty_id', $facultyId)->select('id')
                );
            });
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
            'index'  => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/baru'),
            'view'   => Pages\ViewCourse::route('/{record}'),
            'edit'   => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
