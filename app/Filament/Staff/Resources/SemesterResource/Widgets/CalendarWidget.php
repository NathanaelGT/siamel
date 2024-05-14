<?php

namespace App\Filament\Staff\Resources\SemesterResource\Widgets;

use App\Enums\SemesterSchedules;
use App\Models\SemesterSchedule;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = SemesterSchedule::class;

    public function fetchEvents(array $info): array
    {
        return SemesterSchedule::query()
            ->whereBetween('date', [$info['start'], $info['end']])
            ->get()
            ->map(function (SemesterSchedule $schedule) {
                $bgColor = match ($schedule->name) {
                    SemesterSchedules::Normal->value     => '#fde047',
                    SemesterSchedules::KRS->value        => '#fef08a',
                    SemesterSchedules::Midterm->value    => '#6366f1',
                    SemesterSchedules::Final->value      => '#8b5cf6',
                    SemesterSchedules::Graduation->value => '#0284c7',
                    default                              => str($schedule->name)->contains('Cuti Bersama')
                        ? '#f87171'
                        : '#fb7185',
                };

                $name = $schedule->name !== SemesterSchedules::Normal->value
                    ? e($schedule->name)
                    : '';

                return EventData::make()
                    ->id($schedule->id)
                    ->title(
                        '<span x-init="$el.parentElement.parentElement.style=\'--fc-event-bg-color:transparent;--fc-event-border-color:transparent\';const fcDay = $el.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement;fcDay.style=\'background-color:' . $bgColor . '\';fcDay.classList.add(\'custom\');$el.style.top=`-${fcDay.firstChild.firstChild.clientHeight}px`" style="text-wrap:balance;position:absolute">' . $name . '</span>'
                    )
                    ->start($schedule->date)
                    ->end($schedule->date);
            })
            ->toArray();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name'),

            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_at'),

                    Forms\Components\DateTimePicker::make('ends_at'),
                ]),
        ];
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
            'height' => 500,
        ];
    }
}
