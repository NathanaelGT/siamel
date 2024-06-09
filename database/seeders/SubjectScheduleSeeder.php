<?php

namespace Database\Seeders;

use App\Enums\WorkingDay;
use App\Models\Semester;
use App\Models\SubjectSchedule;
use Illuminate\Support\Facades\DB;

class SubjectScheduleSeeder extends Seeder
{
    public function setupRun(): void
    {
        foreach (Semester::all(['id', 'parity', 'year']) as $semester) {
            $this->dispatcher->run($semester);
        }
    }

    public function run(Semester $semester): void
    {
        $semesterScheduleQuery = $semester->schedules()->where('name', 'Perkuliahan');
        /** @var \Illuminate\Support\Carbon $firstDay */
        $firstDay = $semesterScheduleQuery->value('date');

        /** @var \Illuminate\Support\Carbon $lastDay */
        $lastDay = $semesterScheduleQuery->orderBy('date', 'desc')->value('date');
        $scheduleCount = $firstDay->diffInWeeks($lastDay);

        $subjects = DB::table('subjects')
            ->join('courses', 'subjects.course_id', '=', 'courses.id')
            ->where('semester_id', $semester->id)
            ->get(['subjects.id', 'day', 'start_time', 'credits']);

        $schedules = [];
        for ($i = 0; $i < $scheduleCount; $i++) {
            $date = $firstDay->addWeek();

            foreach ($subjects as $subject) {
                $schedules[] = [
                    'subject_id' => $subject->id,
                    'start_time' => ($start = $date->clone()
                        ->setTimeFrom($subject->start_time)
                        ->addDays(match ($subject->day) {
                            WorkingDay::Monday->value    => 0,
                            WorkingDay::Tuesday->value   => 1,
                            WorkingDay::Wednesday->value => 2,
                            WorkingDay::Thursday->value  => 3,
                            WorkingDay::Friday->value    => 4,
                        }))
                        ->toDateTimeString(),
                    'end_time'   => $start->addMinutes($subject->credits * 50)->toDateTimeString(),
                    'meeting_no' => $i + 1,
                ];
            }
        }

        foreach (array_chunk($schedules, 5000) as $chunk) {
            SubjectSchedule::query()->insert($chunk);
        }
    }
}
