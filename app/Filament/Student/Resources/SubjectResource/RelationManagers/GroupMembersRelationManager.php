<?php

namespace App\Filament\Student\Resources\SubjectResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Student\Resources\SubjectResource;
use App\Models\SubjectGroupMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/** @property-read \App\Models\Subject $ownerRecord */
class GroupMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'groupMembers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('student.account.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        /** @var \App\Models\SubjectGroup $group */
        $group = auth()->user()
            ->student
            ->groups()
            ->where('subject_groups.subject_id', $this->ownerRecord->id)
            ->firstOrFail();

        $lcGroupName = Str::lower($group->name);

        return $table
            ->recordTitleAttribute('student.account.name')
            ->paginated(false)
            ->heading("Anggota $lcGroupName")
            ->columns([
                Tables\Columns\TextColumn::make('student.id'),

                Tables\Columns\TextColumn::make('student.account.name'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('leave')
                    ->label('Keluar')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription("Apakah Anda yakin ingin keluar dari $lcGroupName?")
                    ->successNotificationTitle("Anda berhasil keluar dari $lcGroupName")
                    ->successRedirectUrl(fn() => SubjectResource::getUrl('view', [$this->ownerRecord]) . '?activeRelationManager=1')
                    ->authorize(fn() => $this->ownerRecord->student_can_manage_group)
                    ->action(function (Tables\Actions\Action $action) use ($group) {
                        $group->members()->detach(auth()->user()->student->id);

                        $action->success();
                    }),
            ])
            ->query(SubjectGroupMember::query()->where('subject_group_id', $group->id));
    }
}
