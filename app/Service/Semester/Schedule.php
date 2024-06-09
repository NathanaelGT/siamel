<?php

namespace App\Service\Semester;

use App\Enums\SemesterSchedules;
use Closure;
use Illuminate\Support\Carbon;

abstract class Schedule
{
    public static function odd(?int $semesterId, int $year, array &$schedules = []): array
    {
        $start = Carbon::create($year, 8);
        $end = Carbon::create($year, 12, 31)->endOfDay();

        foreach (Holiday::fetch($year) as $holiday) {
            /** @var \Illuminate\Support\Carbon $date */
            $date = $holiday['date'];

            if ($date->between($start, $end) || $date->month === 1) {
                $date = $date->toDateString();

                $schedules[$date] = [
                    'name'        => $holiday['name'],
                    'date'        => $date,
                    'semester_id' => $semesterId,
                ];
            }
        }

        $addSchedule = static::createAddSchedule($schedules, $semesterId);
        $addSchedules = static::createAddSchedules($addSchedule);

        $addSchedule(SemesterSchedules::Midterm, Carbon::parse("second Saturday of July $year"));

        $date = Carbon::parse("last Friday of August $year")->subDays(12);
        $addSchedules(SemesterSchedules::KRS, $date);

        $date->addWeeks(7)->addDays(2);
        $addSchedules(SemesterSchedules::Midterm, $date);

        $addSchedule(SemesterSchedules::Midterm, Carbon::parse("second Saturday of October $year"));

        $date = Carbon::parse("last Friday of October $year")->subDays(11);
        $addSchedules(SemesterSchedules::Final, $date);

        $start = Carbon::parse("last Sunday of August $year");
        $end = $date->subWeeks(2)->toDateString();
        while ($start->lessThan($end)) {
            for ($i = 0; $i < 5; $i++) {
                $d = $start->addDay()->toDateString();
                if (! isset($schedules[$d])) {
                    $schedules[$d] = [
                        'name'        => SemesterSchedules::Normal->value,
                        'date'        => $d,
                        'semester_id' => $semesterId,
                    ];
                }
            }

            $start->addDays(2);
        }

        return $schedules;
    }

    public static function even(?int $semesterId, int $year, array &$schedules = []): array
    {
        $start = Carbon::create($year, 2);
        $end = Carbon::create($year, 7, 31)->endOfDay();

        foreach (Holiday::fetch($year) as $holiday) {
            /** @var \Illuminate\Support\Carbon $date */
            $date = $holiday['date'];

            if ($date->between($start, $end)) {
                $date = $date->toDateString();

                $schedules[$date] = [
                    'name'        => $holiday['name'],
                    'date'        => $date,
                    'semester_id' => $semesterId,
                ];
            }
        }

        $addSchedule = static::createAddSchedule($schedules, $semesterId);
        $addSchedules = static::createAddSchedules($addSchedule);

        $date = Carbon::parse("first Sunday of February $year");
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
                        'name'        => SemesterSchedules::Normal->value,
                        'date'        => $d,
                        'semester_id' => $semesterId,
                    ];
                }
            }

            $startD->addDays(2);
        }

        return $schedules;
    }

    protected static function createAddSchedule(array &$schedules, int $semesterId): Closure
    {
        return function (SemesterSchedules $name, Carbon $date) use (&$schedules, $semesterId) {
            $d = $date->toDateString();

            if (! isset($schedules[$d])) {
                $schedules[$d] = [
                    'name'        => $name->value,
                    'date'        => $d,
                    'semester_id' => $semesterId,
                ];
            }
        };
    }

    protected static function createAddSchedules(Closure $addSchedule): Closure
    {
        return function (SemesterSchedules $name, Carbon $date) use ($addSchedule) {
            for ($i = 0; $i < 5; $i++) {
                $addSchedule($name, $date->addDay());
            }
            $date->addDays(2);
            for ($i = 0; $i < 5; $i++) {
                $addSchedule($name, $date->addDay());
            }
        };
    }
}
