<?php

namespace App\Filament\Professor\Resources\SubjectResource\Pages;

use App\Filament\Professor\Resources\SubjectResource;
use App\Models\Subject;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

/** @property-read \App\Models\SubjectGroup $record */
class Group extends ViewRecord
{
    #[Locked]
    public Subject $subject;

    protected static string $resource = SubjectResource::class;

    public function getRelationManagers(): array
    {
        return [
            SubjectResource\RelationManagers\GroupMembersRelationManager::class,
        ];
    }

    protected function resolveRecord(int | string $key): Model
    {
        return $this->subject
            ->groups()
            ->findOrFail($key)
            ->load('members')
            ->setRelation('subject', $this->subject);
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getBreadcrumbs(): array
    {
        $url = fn(string $name = 'index', array $parameters = []) => SubjectResource::getUrl($name, $parameters);

        $groupRelationManagerIndex = array_search(
            SubjectResource\RelationManagers\GroupsRelationManager::class,
            (new SubjectResource\Pages\ViewSubject)->getRelationManagers()
        );

        $breadcrumbs = [];
        $breadcrumbs[$url()] = SubjectResource::getBreadcrumb();
        $breadcrumbs[$viewUrl = $url('view', [$this->subject])] = $this->subject->title . ' - ' . $this->subject->semester->academic_year;
        $breadcrumbs["$viewUrl?activeRelationManager=$groupRelationManagerIndex"] = SubjectResource\RelationManagers\GroupsRelationManager::getTitle($this->subject, SubjectResource\Pages\ViewSubject::class);
        $breadcrumbs[] = $this->getTitle();

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }
}
