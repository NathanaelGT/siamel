<?php

namespace App\Filament\Student\Resources\SubjectResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Student\Resources\SubjectResource;
use App\Models\SubjectGroup;
use App\Models\SubjectGroupMember;
use App\Service\SubjectGroup\Name;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/** @property-read \App\Models\Subject $ownerRecord */
class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name'),

                Tables\Columns\TextColumn::make('subject_group_members_count')
                    ->formatStateUsing(fn(int $state) => "$state/{$this->ownerRecord->group_max_members}"),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Buat kelompok baru')
                    ->modalSubmitActionLabel('Buat dan Gabung')
                    ->successNotificationTitle('Anda berhasil membuat membuat dan bergabung ke kelompok baru')
                    ->successRedirectUrl(fn() => SubjectResource::getUrl('view', [$this->ownerRecord]) . '?activeRelationManager=1')
                    ->authorize(fn() => $this->ownerRecord->student_can_create_group)
                    ->action(function (Tables\Actions\Action $action) {
                        $group = $this->ownerRecord->groups()->create([
                            'name' => Name::generate($this->ownerRecord),
                        ]);

                        $group->members()->attach(auth()->user()->student->id);

                        $action->success();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(null)
                    ->modalHeading('')
                    ->modalCancelAction(false)
                    ->modalContent(function (SubjectGroup $group) {
                        return new HtmlString(Blade::render('@livewire($widget, $props)', [
                            'widget' => SubjectResource\Widgets\SubjectGroupMemberTable::class,
                            'props'  => [
                                'group' => $group,
                            ],
                        ]));
                    }),

                Tables\Actions\Action::make('join')
                    ->label('Gabung')
                    ->icon('heroicon-m-arrow-left-end-on-rectangle')
                    ->requiresConfirmation()
                    ->modalDescription(fn(SubjectGroup $record) => 'Apakah Anda yakin ingin bergabung dengan ' . Str::lower($record->name) . '?')
                    ->successNotificationTitle(fn(SubjectGroup $record) => 'Anda berhasil bergabung dengan ' . Str::lower($record->name))
                    ->successRedirectUrl(fn() => SubjectResource::getUrl('view', [$this->ownerRecord]) . '?activeRelationManager=1')
                    ->authorize(fn() => $this->ownerRecord->student_can_manage_group)
                    ->disabled(fn(SubjectGroup $record) => $record->subject_group_members_count >= $this->ownerRecord->group_max_members)
                    ->action(function (Tables\Actions\Action $action, SubjectGroup $record) {
                        $record->members()->attach(auth()->user()->student->id);

                        $action->success();
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount('subjectGroupMembers');
            });
    }

    protected function can(string $action, ?Model $record = null): bool
    {
        if (
            ($record instanceof SubjectGroup || $record instanceof SubjectGroupMember) &&
            ! $this->ownerRecord->student_can_manage_group
        ) {
            return false;
        }

        return parent::can($action, $record);
    }

    protected function canCreate(): bool
    {
        if (! $this->ownerRecord->student_can_create_group) {
            return false;
        }

        return Gate::forUser(Filament::auth()->user())
            ->authorize('create', [SubjectGroup::class, $this->ownerRecord])
            ->allowed();
    }

    protected function canView(Model $record): bool
    {
        return true;
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
