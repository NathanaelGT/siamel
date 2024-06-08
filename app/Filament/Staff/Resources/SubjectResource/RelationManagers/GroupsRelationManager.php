<?php

namespace App\Filament\Staff\Resources\SubjectResource\RelationManagers;

use App\Filament\RelationManager;
use App\Filament\Student\Resources\SubjectResource\Widgets\SubjectGroupMemberTable;
use App\Models\SubjectGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/** @property-read \App\Models\Subject $ownerRecord */
class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->modelLabel('kelompok')
            ->recordTitleAttribute('name')
            ->emptyStateHeading(fn() => $this->ownerRecord->group_max_members === null
                ? 'Tidak ada kelompok'
                : 'Belum ada kelompok')
            ->emptyStateDescription(null)
            ->columns([
                Tables\Columns\TextColumn::make('name'),

                Tables\Columns\TextColumn::make('subject_group_members_count'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(null)
                    ->modalHeading('')
                    ->modalCancelAction(false)
                    ->modalContent(function (SubjectGroup $group) {
                        return new HtmlString(Blade::render('@livewire($widget, $props)', [
                            'widget' => SubjectGroupMemberTable::class,
                            'props'  => [
                                'group' => $group,
                            ],
                        ]));
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount('subjectGroupMembers');
            });
    }
}
