<?php

namespace Database\Seeders;

use App\Enums\CourseParity;
use App\Enums\Parity;
use App\Enums\StudentStatus;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentSubject;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class StudentSubjectSeeder extends Seeder
{
    public function setupRun(): void
    {
        $now = now();

        $parity = match ($now->month) {
            8, 9, 10, 11, 12, 1 => Parity::Odd,
            2, 3, 4, 5, 6, 7    => Parity::Even,
        };

        foreach (Semester::all(['id', 'parity', 'year']) as $semester) {
            $this->dispatcher->run(
                $semester,
                $semester->year === $now->year && $semester->parity === $parity
            );
        }
    }

    public function _run(Semester $semester, bool $isCurrentSemester): void
    {
        $semesterScheduleQuery = $semester->schedules()->where('name', 'KRS');
        /** @var \Illuminate\Support\Carbon $firstDay */
        $firstDay = $semesterScheduleQuery->value('date');

        /** @var \Illuminate\Support\Carbon $lastDay */
        $lastDay = $semesterScheduleQuery->orderBy('date', 'desc')->value('date');

        $registrationPeriod = iterator_to_array(
            CarbonPeriod::between($firstDay, $lastDay)->map(function (Carbon $date) {
                return $date->format('Y-m-d');
            })
        );

        $studentMap = DB::table('students')
            ->where('status', StudentStatus::Active)
            ->get(['id', 'study_program_id'])
            ->groupBy(['study_program_id', fn(stdClass $student) => '20' . substr($student->id, 0, 2)]);

        $studyProgramSubjects = DB::table('subjects')
            ->join('courses', function (JoinClause $join) use ($semester) {
                $join->on('subjects.course_id', '=', 'courses.id')
                    ->whereIn('courses.semester_parity', [
                        $semester->parity,
                        CourseParity::Null,
                    ]);
            })
            ->join('semesters', 'subjects.semester_id', '=', 'semesters.id')
            ->where('semester_id', $semester->id)
            ->get([
                'slug',
                'subjects.id',
                'course_id',
                'capacity',
                'year',
                'day',
                'start_time',
                'study_program_id',
                'semester_required',
                'credits',
            ])
            ->groupBy(['study_program_id', 'course_id']);

        /** @var \Illuminate\Support\Collection $subjectGroups */
        foreach ($studyProgramSubjects as $studyProgramId => $subjectGroups) {
            /** @var \Illuminate\Support\Collection $studentCohorts */
            $studentCohorts = $studentMap->get($studyProgramId);

            $studentSchedule = [];
            $studentCredits = [];
            $studentSubject = [];

            /** @var \Illuminate\Support\Collection $subjects */
            foreach ($subjectGroups->shuffle() as $courseId => $subjects) {
                /** @var \Illuminate\Support\Collection $students */
                $students = $studentCohorts[$subjects[0]->year];

                $studentCountMap = $subjects->mapWithKeys(fn(stdClass $subject) => [$subject->id => 0])->all();
                $subjectCount = $subjects->count();

                for ($studentIndex = 0, $i = 0; true; $i++) {
                    if ($this->faker->boolean(15)) {
                        continue;
                    }

                    $subject = $subjects[$i % $subjectCount];
                    if ($studentCountMap[$subject->id] >= $subject->capacity) {
                        foreach ($subjects as $subject) {
                            if ($studentCountMap[$subject->id] < $subject->capacity) {
                                continue 2;
                            }
                        }

                        // semua kelas sudah penuh
                        break;
                    }

                    /** @var ?Student $student */
                    $student = $students->get($studentIndex++);
                    if ($student === null) {
                        // mahasiswanya sudah terdaftar semua
                        break;
                    }

                    if (isset($studentSchedule[$student->id][$subject->day][$subject->start_time])) {
                        continue;
                    }

                    if (! isset($studentCredits[$student->id])) {
                        $studentCredits[$student->id] = 0;
                    } elseif (($studentCredits[$student->id] + $subject->credits) > 24) {
                        continue;
                    }

                    $studentSchedule[$student->id][$subject->day][$subject->start_time] = true;
                    $studentCredits[$student->id] += $subject->credits;
                    $studentCountMap[$subject->id]++;

                    $studentSubject[] = [
                        'student_id'    => $student->id,
                        'subject_id'    => $subject->id,
                        'registered_at' =>
                            Arr::random($registrationPeriod) . ' ' .
                            mt_rand(7, 16) . ':' .
                            mt_rand(0, 59) . ':' .
                            mt_rand(0, 59),
                    ];
                }
            }

            foreach (array_chunk($studentSubject, 4000) as $chunkedStudentSubject) {
                StudentSubject::query()->insert($chunkedStudentSubject);
            }
        }
    }

    public function run(Semester $semester, bool $isCurrentSemester): void
    {
        $semesterScheduleQuery = $semester->schedules()->where('name', 'KRS');
        /** @var \Illuminate\Support\Carbon $firstDay */
        $firstDay = $semesterScheduleQuery->value('date');

        /** @var \Illuminate\Support\Carbon $lastDay */
        $lastDay = $semesterScheduleQuery->orderBy('date', 'desc')->value('date');

        $registrationPeriod = iterator_to_array(
            CarbonPeriod::between($firstDay, $lastDay)->map(function (Carbon $date) {
                return $date->format('Y-m-d');
            })
        );

        $studentMap = DB::table('students')
            ->where('status', StudentStatus::Active)
            ->get(['id', 'study_program_id'])
            ->groupBy(['study_program_id', fn(stdClass $student) => '20' . substr($student->id, 0, 2)]);

        $studyProgramSubjects = DB::table('subjects')
            ->join('courses', function (JoinClause $join) use ($semester) {
                $join->on('subjects.course_id', '=', 'courses.id')
                    ->whereIn('courses.semester_parity', [
                        $semester->parity,
                        CourseParity::Null,
                    ]);
            })
            ->join('semesters', 'subjects.semester_id', '=', 'semesters.id')
            ->where('semester_id', $semester->id)
            ->get([
                'slug',
                'subjects.id',
                'course_id',
                'capacity',
                'parity',
                'year',
                'day',
                'start_time',
                'study_program_id',
                'semester_required',
                'credits',
            ])
            ->groupBy(['study_program_id', 'course_id']);

        /** @var \Illuminate\Support\Collection $subjectGroups */
        foreach ($studyProgramSubjects as $studyProgramId => $subjectGroups) {
            /** @var \Illuminate\Support\Collection $studentCohorts */
            $studentCohorts = $studentMap->get($studyProgramId)->sortKeys(descending: true);

            $studentSchedule = [];
            $studentCredits = [];
            $studentSubject = [];
            $studentCountMap = [];

            foreach ($studentCohorts as $year => $students) {
                $studentSemester = ($semester->year - $year) * 2;
                if ($semester->parity === Parity::Odd) {
                    $studentSemester++;
                }

                /** @var \Illuminate\Support\Collection $subjects */
                foreach ($subjectGroups->shuffle() as $courseId => $subjects) {
                    if (
                        ($subjects[0]->semester_required > $studentSemester) ||
                        ($studentSemester === 1 && $subjects[0]->parity === Parity::Even->value)
                    ) {
                        continue;
                    }

                    $subjectCount = $subjects->count();

                    for ($studentIndex = 0, $i = 0; true; $i++) {
                        if ($this->faker->boolean(15)) {
                            continue;
                        }

                        $subject = $subjects[$i % $subjectCount];
                        if (! isset($studentCountMap[$subject->id])) {
                            $studentCountMap[$subject->id] = 0;
                        } elseif ($studentCountMap[$subject->id] >= $subject->capacity) {
                            foreach ($subjects as $subject) {
                                if ($studentCountMap[$subject->id] < $subject->capacity) {
                                    continue 2;
                                }
                            }

                            // semua kelas sudah penuh
                            break;
                        }

                        /** @var ?Student $student */
                        $student = $students->get($studentIndex++);
                        if ($student === null) {
                            // mahasiswanya sudah terdaftar semua
                            break;
                        }

                        if (isset($studentSchedule[$student->id][$subject->day][$subject->start_time])) {
                            continue;
                        }

                        if (! isset($studentCredits[$student->id])) {
                            $studentCredits[$student->id] = 0;
                        } elseif (($studentCredits[$student->id] + $subject->credits) > 24) {
                            continue;
                        }

                        $studentSchedule[$student->id][$subject->day][$subject->start_time] = true;
                        $studentCredits[$student->id] += $subject->credits;
                        $studentCountMap[$subject->id]++;

                        $studentSubject[] = [
                            'student_id'    => $student->id,
                            'subject_id'    => $subject->id,
                            'registered_at' =>
                                Arr::random($registrationPeriod) . ' ' .
                                mt_rand(7, 16) . ':' .
                                mt_rand(0, 59) . ':' .
                                mt_rand(0, 59),
                        ];
                    }
                }
            }

            foreach (array_chunk($studentSubject, 4000) as $chunked) {
                StudentSubject::query()->insert($chunked);
            }
        }
    }
}
