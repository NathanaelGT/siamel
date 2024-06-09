<?php

namespace App\Filament\Staff\Resources\SemesterResource\Pages;

use App\Enums\Parity;
use App\Filament\Staff\Resources\SemesterResource;
use App\Models\Semester;
use App\Service\Semester\Schedule;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSemesters extends ListRecords
{
    protected static string $resource = SemesterResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            SemesterResource\Widgets\CalendarWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Buat')
                ->requiresConfirmation()
                ->modalDescription('Apakah Anda ingin membuat semester selanjutnya?')
                ->authorize('create', Semester::class)
                ->hidden(once(fn() => Semester::query()
                    ->where($this->nextSemesterYearAndParity())
                    ->exists()))
                ->action(function (Actions\Action $action) {
                    $semester = Semester::create($this->nextSemesterYearAndParity())->refresh();

                    $semester->schedules()->createMany(match ($semester->parity) {
                        Parity::Odd  => Schedule::odd($semester->id, $semester->year),
                        Parity::Even => Schedule::even($semester->id, $semester->year),
                    });

                    $action->successNotificationTitle("$semester->academic_year berhasil dibuat");
                    $action->success();
                }),
        ];
    }

    protected function nextSemesterYearAndParity(): array
    {
        $date = now()->addMonths(6);

        $parity = match ($date->month) {
            8, 9, 10, 11, 12, 1 => Parity::Odd,
            2, 3, 4, 5, 6, 7    => Parity::Even,
        };

        return [
            'year'   => $date->year,
            'parity' => $parity,
        ];
    }
}
