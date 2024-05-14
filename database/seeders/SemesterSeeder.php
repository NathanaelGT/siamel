<?php

namespace Database\Seeders;

use App\Enums\Parity;
use App\Enums\SemesterSchedules;
use App\Models\Semester;
use App\Models\SemesterSchedule;
use App\Service\Semester\Holiday;
use Illuminate\Support\Carbon;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $start = 2019;
        $end = now()->year;

        $parities = array_reverse(Parity::cases());

        $semesters = [];
        $schedules = [];

        for ($year = $start; $year <= $end; $year++) {
            foreach ($parities as $parity) {
                $semesters[] = [
                    'parity' => $parity,
                    'year'   => $year,
                ];
            }

            foreach (Holiday::fetch($year) as $holiday) {
                $schedules[$holiday['date']] = $holiday;
            }

            $addSchedule = function (SemesterSchedules $name, Carbon $date) use (&$schedules) {
                $d = $date->toDateString();

                if (! isset($schedules[$d])) {
                    $schedules[$d] = [
                        'name' => $name->value,
                        'date' => $d,
                    ];
                }
            };

            $addSchedules = function (SemesterSchedules $name, Carbon $date) use ($addSchedule) {
                for ($i = 0; $i < 5; $i++) {
                    $addSchedule($name, $date->addDay());
                }
                $date->addDays(2);
                for ($i = 0; $i < 5; $i++) {
                    $addSchedule($name, $date->addDay());
                }
            };

            if ($year !== $start) {
                $date = Carbon::parse("first Friday of February $year")->subDays(12);
                $addSchedules(SemesterSchedules::KRS, $date);

                $addSchedule(SemesterSchedules::Graduation, Carbon::parse("last Saturday of February $year"));

                $date = $date->addWeeks(7)->addDays(2);
                $addSchedules(SemesterSchedules::Midterm, $date);

                $date = $date->addWeeks(9)->addDays(2);
                $addSchedules(SemesterSchedules::Final, $date);

                $startD = Carbon::parse("second Friday of February $year")->subDays(5);
                $endD = $date->subWeeks(3)->toDateString();
                while ($startD->lessThan($endD)) {
                    for ($i = 0; $i < 5; $i++) {
                        $d = $startD->addDay()->toDateString();
                        if (! isset($schedules[$d])) {
                            $schedules[$d] = [
                                'name' => SemesterSchedules::Normal->value,
                                'date' => $d,
                            ];
                        }
                    }

                    $startD->addDays(2);
                }
            }

            if ($year !== $end) {
                $addSchedule(SemesterSchedules::Midterm, Carbon::parse("second Saturday of July $year"));

                $date = Carbon::parse("last Friday of August $year")->subDays(12);
                $addSchedules(SemesterSchedules::KRS, $date);

                $date->addWeeks(7)->addDays(2);
                $addSchedules(SemesterSchedules::Midterm, $date);

                $addSchedule(SemesterSchedules::Midterm, Carbon::parse("second Saturday of October $year"));

                $date = Carbon::parse("last Friday of October $year")->subDays(11);
                $addSchedules(SemesterSchedules::Final, $date);

//                $start = Carbon::parse("last Sunday of August $year");
//                $end = $date->subWeeks(2)->toDateString();
//                while ($start->lessThan($end)) {
//                    for ($i = 0; $i < 5; $i++) {
//                        $d = $start->addDay()->toDateString();
//                        if (! isset($schedules[$d])) {
//                            $schedules[$d] = [
//                                'name'       => SemesterSchedules::Normal->value,
//                                'start_date' => $d,
//                                'end_date'   => $d,
//                            ];
//                        }
//                    }
//
//                    $start->addDays(2);
//                }
            }
        }

        $semesters = array_slice($semesters, 1, -1);

        Semester::query()->insert($semesters);
        SemesterSchedule::query()->insert(collect($schedules)->sortKeys()->values()->all());
    }
}
