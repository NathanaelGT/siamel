<?php

namespace Database\Seeders;

use App\Enums\WorkingDay;
use App\Models\Course;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Models\Subject;
use App\Service\Subject\Slug;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Collection $courseProfessorMap */
        $courseProfessorMap = DB::query()
            ->from('course_professor')
            ->get()
            ->groupBy('course_id')
            ->map(fn(Collection $col) => $col->map->professor_id);

        $courseNames = Course::query()->pluck('name', 'id');

        $facultyIds = StudyProgram::query()->pluck('faculty_id');

        $rooms = DB::query()
            ->from('rooms')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->whereIn('buildings.faculty_id', $facultyIds->unique()->all())
            ->select(['rooms.id', 'rooms.capacity', 'buildings.faculty_id'])
            ->get()
            ->groupBy('faculty_id');

        $workingDays = WorkingDay::cases();

        $subjects = [];

        Semester::all(['id', 'parity', 'year'])->each(
            function (Semester $semester)
            use ($courseProfessorMap, $courseNames, $facultyIds, $rooms, $workingDays, &$subjects) {
                $existCourseMap = [];

                foreach ($facultyIds as $facultyId) {
                    foreach ($workingDays as $workingDay) {
                        $startTimes = match ($workingDay) {
                            WorkingDay::Friday => ['07:00', '09:30'],
                            default            => ['07:00', '09:30', '13:00', '15:30'],
                        };

                        if ($this->faker->boolean(25)) {
                            unset($startTimes[0]);
                        }

                        if ($this->faker->boolean(25)) {
                            unset($startTimes[count($startTimes) - 1]);
                        }

                        foreach ($startTimes as $startTime) {
                            if ($this->faker->boolean(15)) {
                                continue;
                            }

                            $room = $rooms[$facultyId]->random();
                            $course = $courseProfessorMap->random(1, preserveKeys: true);
                            $courseId = $course->keys()[0];
                            $professorIds = $course->values()[0];

                            $courseName = $courseNames[$courseId];

                            if (isset($existCourseMap[$courseName])) {
                                $existCourseMap[$courseName]++;
                            } else {
                                $existCourseMap[$courseName] = 1;
                            }

                            $parallel = chr(64 + $existCourseMap[$courseName]);
                            $code = '081';
                            $slug = Slug::generate(
                                $courseName,
                                $semester->parity,
                                $semester->year,
                                $parallel,
                                $code
                            );

                            $subjects[] = [
                                'course_id'    => $courseId,
                                'semester_id'  => $semester->id,
                                'professor_id' => $professorIds->random(),
                                'room_id'      => $room->id,
                                'capacity'     => $room->capacity,
                                'parallel'     => $parallel,
                                'code'         => $code,
                                'slug'         => $slug,
                                'day'          => $workingDay->value,
                                'start_time'   => $startTime,
                            ];
                        }
                    }
                }
            }
        );

        Subject::query()->insert($subjects);
//        dump(count($subjects));
//        collect($subjects)->chunk(25)->each(function (Collection $subjects) {
//            Subject::query()->insert($subjects->all());
//        });
    }
}
