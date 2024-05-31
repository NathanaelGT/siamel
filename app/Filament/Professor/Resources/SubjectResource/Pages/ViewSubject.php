<?php

namespace App\Filament\Professor\Resources\SubjectResource\Pages;

use App\Filament\Professor\Resources\SubjectResource;
use App\Models\Subject;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

/** @property-read Subject $record */
class ViewSubject extends ViewRecord
{
    protected static string $resource = SubjectResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('room.full_name'),

                Infolists\Components\TextEntry::make('day_time')
                    ->label('Waktu')
                    ->default(fn(Subject $subject) => $subject->day->value . ' ' . $subject->time),
            ]);
    }

    public function getRelationManagers(): array
    {
        return [
            //
        ];
    }

    public function getTitle(): string
    {
        return $this->record->title . ' - ' . $this->record->semester->academic_year;
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