<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use stdClass;

class AttendanceSeeder extends Seeder
{
    public function setupRun(): void
    {
        foreach (Semester::pluck('id') as $semesterId) {
            $this->dispatcher->run($semesterId);
        }
    }

    public function run(int $semesterId): void
    {
        $subjectScheduleMap = DB::table('subject_schedules')
            ->join('subjects', 'subjects.id', '=', 'subject_schedules.subject_id')
            ->where('semester_id', $semesterId)
            ->get(['subject_schedules.id', 'subject_id', 'end_time'])
            ->reduce(function (array $subjectScheduleMap, stdClass $subjectSchedule) {
                $subjectScheduleMap[$subjectSchedule->subject_id]['ids'][] = $subjectSchedule->id;
                $subjectScheduleMap[$subjectSchedule->subject_id]['endTime'] = $subjectSchedule->end_time;

                return $subjectScheduleMap;
            }, []);

        $subjectStudentMap = DB::table('student_subject')
            ->whereIn('subject_id', array_keys($subjectScheduleMap))
            ->get(['subject_id', 'student_id'])
            ->reduce(function (array $subjectStudentMap, stdClass $studentSubject) {
                $subjectStudentMap[$studentSubject->subject_id][] = $studentSubject->student_id;

                return $subjectStudentMap;
            }, []);

        $statuses = [
            ...array_fill(0, 36, AttendanceStatus::Present->value), // 90%
            AttendanceStatus::Sick, // 5%
            AttendanceStatus::Sick,
            AttendanceStatus::Permit, // 2.5%
            AttendanceStatus::Absent, // 2.5%
        ];

        $attendances = [];
        foreach ($subjectScheduleMap as $subjectId => $schedule) {
            if (! isset($subjectStudentMap[$subjectId])) {
                continue;
            }

            $studentIds = $subjectStudentMap[$subjectId];

            foreach ($schedule['ids'] as $subjectScheduleId) {
                foreach ($studentIds as $studentId) {
                    $attendances[] = [
                        'subject_schedule_id' => $subjectScheduleId,
                        'student_id'          => $studentId,
                        'status'              => $this->faker->randomElement($statuses),
                        'date'                => $schedule['endTime'],
                    ];
                }
            }
        }

        foreach (array_chunk($attendances, 8000) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }
}
