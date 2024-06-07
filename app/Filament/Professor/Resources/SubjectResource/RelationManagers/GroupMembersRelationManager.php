<?php

namespace App\Filament\Professor\Resources\SubjectResource\RelationManagers;

use App\Filament\RelationManager;
use App\Models\Student;
use App\Models\SubjectGroup;
use App\Models\SubjectGroupMember;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** @property-read \App\Models\SubjectGroup $ownerRecord */
class GroupMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'subjectGroupMembers';

    public function table(Table $table): Table
    {
        $joinedIds = $this->ownerRecord
            ->subject
            ->groups
            ->load('subjectGroupMembers')
            ->pluck('subjectGroupMembers')
            ->flatten()
            ->pluck('student_id', 'student_id');

        $studentOptions = $this->ownerRecord
            ->subject
            ->students
            ->load('account:id,name')
            ->mapWithKeys(fn(Student $student) => [$student->id => $student])
            ->diffKeys($joinedIds)
            ->map(fn(Student $student) => "$student->id: {$student->account->name}");

        $maxItems = $this->ownerRecord->subject->group_max_members - $this->ownerRecord->members()->count();

        return $table
            ->paginated(false)
            ->recordTitleAttribute('name')
            ->emptyStateHeading('Tidak ada anggota kelompok')
            ->emptyStateDescription(null)
            ->columns([
                Tables\Columns\TextColumn::make('student.id'),

                Tables\Columns\TextColumn::make('student.account.name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambahkan Anggota')
                    ->modalHeading('Tambahkan anggota ke kelompok')
                    ->modalSubmitActionLabel('Tambahkan')
                    ->successNotificationTitle('Anggota berhasil ditambahkan')
                    ->createAnother(false)
                    ->disabled($maxItems === 0)
                    ->form(fn(Form $form) => $form
                        ->model(SubjectGroup::class)
                        ->schema([
                            Forms\Components\Select::make('students')
                                ->hiddenLabel()
                                ->multiple()
                                ->optionsLimit(100)
                                ->options($studentOptions)
                                ->required()
                                ->maxItems($maxItems),
                        ]))
                    ->using(fn(array $data) => DB::transaction(function () use ($data) {
                        $this->ownerRecord->members()->attach($data['students']);

                        return $this->ownerRecord;
                    })),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Keluarkan')
                    ->icon(FilamentIcon::resolve('actions::detach-action') ?? 'heroicon-m-x-mark')
                    ->modalIcon(FilamentIcon::resolve('actions::detach-action.modal') ?? 'heroicon-o-x-mark')
                    ->modalHeading(function (SubjectGroupMember $record) {
                        $student = $record->student->id . ' - ' . $record->student->account->name;

                        return "Keluarkan $student dari {$this->ownerRecord->name}";
                    })
                    ->successNotificationTitle(function (SubjectGroupMember $record) {
                        $student = $record->student->id . ' - ' . $record->student->account->name;

                        return "$student berhasil dikeluarkan dari {$this->ownerRecord->name}";
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Keluarkan yang dipilih')
                    ->icon(FilamentIcon::resolve('actions::detach-action') ?? 'heroicon-m-x-mark')
                    ->modalIcon(FilamentIcon::resolve('actions::detach-action.modal') ?? 'heroicon-o-x-mark')
                    ->successNotificationTitle('Anggota yang dipilih berhasil dikeluarkan'),
            ]);
    }

    protected function canCreate(): bool
    {
        return Gate::forUser(Filament::auth()->user())
            ->authorize('create', [SubjectGroup::class, $this->ownerRecord->subject])
            ->allowed();
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
