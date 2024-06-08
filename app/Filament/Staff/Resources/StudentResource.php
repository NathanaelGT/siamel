<?php

namespace App\Filament\Staff\Resources;

use App\Enums\Gender;
use App\Enums\StudentStatus;
use App\Filament\Resource;
use App\Filament\Staff\Resources\StudentResource\Pages;
use App\Filament\Staff\Resources\StudentResource\RelationManagers;
use App\Filament\Tables\Columns\AvatarColumn;
use App\Models\Student;
use App\Models\StudyProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = 'Pengguna';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Forms\Components\Tabs::make('tabs')->tabs([
                Forms\Components\Tabs\Tab::make('Data Mahasiswa')->schema(fn(Component $livewire) => array_filter([
                    Forms\Components\TextInput::make('account.name')
                        ->validationAttribute('Nama mahasiswa')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('account.email')
                        ->validationAttribute('Email mahasiswa')
                        ->disabledOn('edit')
                        ->email()
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('account.phone_number')
                        ->validationAttribute('Nomor telepon mahasiswa')
                        ->tel()
                        ->required()
                        ->maxLength(16),

                    Forms\Components\Select::make('account.gender')
                        ->validationAttribute('Jenis kelamin mahasiswa')
                        ->options(Gender::class)
                        ->searchable()
                        ->required(),

                    $livewire instanceof RelationManager ? null :
                        Forms\Components\Select::make('study_program_id')
                            ->disabledOn('edit')
                            ->relationship('studyProgram', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                    Forms\Components\TextInput::make('hometown')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('enrollment_type')
                        ->disabledOn('edit')
                        ->required()
                        ->maxLength(255),
                ])),

                Forms\Components\Tabs\Tab::make('Data Orang Tua/Wali')->schema([
                    Forms\Components\TextInput::make('parent_name')
                        ->label('Nama')
                        ->validationAttribute('Nama orang tua/wali')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('parent_phone')
                        ->label('Nomor telepon')
                        ->validationAttribute('Nomor telepon orang tua/wali')
                        ->tel()
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('parent_address')
                        ->label('Alamat')
                        ->validationAttribute('Alamat orang tua/wali')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('parent_job')
                        ->label('Pekerjaan')
                        ->validationAttribute('Pekerjaan orang tua/wali')
                        ->required()
                        ->maxLength(255),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50])
            ->defaultSort('id', 'desc')
            ->columns([
                AvatarColumn::make('account.avatar_url')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('studyProgram.name')
                    ->hidden(fn(Component $livewire) => $livewire instanceof RelationManager)
                    ->numeric()
                    ->sortable()
                    ->toggleable(fn(Component $livewire) => ! $livewire instanceof RelationManager),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(StudentStatus::badgeColor(...))
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('study_program_id')
                    ->hidden(fn(Component $livewire) => $livewire instanceof RelationManager)
                    ->options(function () {
                        $facultyId = Auth::user()->info->faculty_id;

                        if ($facultyId !== null) {
                            return StudyProgram::query()
                                ->where('faculty_id', $facultyId)
                                ->pluck('name', 'id')
                                ->all();
                        }

                        return StudyProgram::query()
                            ->with(['faculty:id,name'])
                            ->get(['id', 'name', 'faculty_id'])
                            ->groupBy('faculty.name')
                            ->map->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->default([StudentStatus::Active->value])
                    ->options(StudentStatus::class)
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
            RelationManagers\ScheduleRelationManager::class,
            RelationManagers\AttendancesRelationManager::class,
            RelationManagers\TranscriptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/baru'),
            'view'   => Pages\ViewStudent::route('/{record}'),
            'edit'   => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
