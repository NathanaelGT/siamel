<?php

namespace App\Filament\Student\Resources\SubjectResource\Pages;

use App\Filament\Student\Resources\SubjectResource;
use App\Filament\Student\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

/** @property-read Subject $record */
class ViewSubject extends ViewRecord
{
    protected static string $resource = SubjectResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make([
                Infolists\Components\TextEntry::make('course.name')
                    ->formatStateUsing(function (Subject $record) {
                        return "{$record->course->name} {$record->parallel}{$record->code}";
                    }),

                Infolists\Components\TextEntry::make('professor.account.name')
                    ->label('Dosen'),

                Infolists\Components\TextEntry::make('room.name')
                    ->label('Tempat')
                    ->formatStateUsing(function (Subject $record) {
                        return "{$record->room->building->abbreviation} {$record->room->name}, {$record->capacity} orang";
                    }),

                Infolists\Components\TextEntry::make('day')
                    ->label('Waktu')
                    ->formatStateUsing(function (Subject $record) {
                        $start = $record->start_time->format('H:i');
                        $end = $record->end_time->format('H:i');

                        return "{$record->day->value}, Pukul {$start}-{$end}";
                    }),
            ])->columns(2),
        ]);
    }

    public function getRelationManagers(): array
    {
        $relationManagers = [
            RelationManagers\PostsRelationManager::class,
        ];

        if ($this->record->group_max_members !== null) {
            static $hasGroup = auth()->user()
                ->student
                ->groups()
                ->where('subject_groups.subject_id', $this->record->id)
                ->exists();

            if ($hasGroup) {
                $relationManagers[] = RelationManagers\GroupMembersRelationManager::class;
            } elseif ($this->record->student_can_manage_group) {
                $relationManagers[] = RelationManagers\GroupsRelationManager::class;
            }
        }

        return $relationManagers;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->title;
    }

    public function getBreadcrumbs(): array
    {
        $url = fn(string $name = 'index', array $parameters = []) => SubjectResource::getUrl($name, $parameters);

        $breadcrumbs = [];
        $breadcrumbs[$url()] = SubjectResource::getBreadcrumb();
        $breadcrumbs[$url('view', [$this->record])] = $this->getTitle();

        if (! is_null($active = $this->activeRelationManager)) {
            $breadcrumbs[] = $this->getRelationManagers()[$active]::getTitle(
                $this->record, static::class,
            );
        }

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }
}
