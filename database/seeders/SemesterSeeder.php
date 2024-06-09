<?php

namespace Database\Seeders;

use App\Enums\Parity;
use App\Models\Semester;
use App\Models\SemesterSchedule;
use App\Service\Semester\Schedule;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $start = 2019;
        $end = now()->year;

        $parities = array_reverse(Parity::cases());

        $semesters = [];

        for ($year = $start; $year <= $end; $year++) {
            foreach ($parities as $parity) {
                $semesters[] = [
                    'parity' => $parity,
                    'year'   => $year,
                ];
            }
        }
        $semesters = array_slice($semesters, 1, -1);

        Semester::query()->insert($semesters);

        $schedules = [];
        foreach (Semester::all(['id', 'parity', 'year']) as $semester) {
            match ($semester->parity) {
                Parity::Odd  => Schedule::odd($semester->id, $semester->year, $schedules),
                Parity::Even => Schedule::even($semester->id, $semester->year, $schedules),
            };
        }

        SemesterSchedule::query()->insert(collect($schedules)->sortKeys()->values()->all());
    }
}
