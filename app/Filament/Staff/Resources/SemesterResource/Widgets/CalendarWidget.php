<?php

namespace App\Filament\Staff\Resources\SemesterResource\Widgets;

use App\Models\SemesterSchedule;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = SemesterSchedule::class;

    protected static bool $isLazy = false;

    public function fetchEvents(array $info): array
    {
        return SemesterSchedule::query()
            ->whereBetween('date', [$info['start'], $info['end']])
            ->get()
            ->map(function (SemesterSchedule $schedule) {
                $name = (string) str(e($schedule->name))
                    ->replace(' ', ' <wbr>');

                return EventData::make()
                    ->id($schedule->id)
                    ->title($name)
                    ->start($schedule->date)
                    ->end($schedule->date);
            })
            ->toArray();
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make()->hidden();
    }

    public function EventContent(): string
    {
        return <<<JS
            function(eventInfo) {
              return { html: eventInfo.event.title }
            }
    JS;
    }

    public function config(): array
    {
        return [
            'height' => 600,
        ];
    }
}
