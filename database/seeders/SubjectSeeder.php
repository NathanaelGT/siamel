<?php

namespace Database\Seeders;

use App\Enums\StudentStatus;
use App\Enums\WorkingDay;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Models\Subject;
use App\Service\Subject\Slug;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use Throwable;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Collection $courseProfessorMap */
        $courseProfessorMap = DB::query()
            ->from('course_professor')
            ->join('professors', 'course_professor.professor_id', '=', 'professors.id')
            ->join('courses', 'course_professor.course_id', '=', 'courses.id')
            ->get([
                'course_id', 'professor_id', 'faculty_id', 'name', 'study_program_id', 'semester_required',
                'semester_parity',
            ])
            ->groupBy('faculty_id')
            ->map(function (Collection $col) {
                return $col->groupBy('semester_parity')->map(function (Collection $col) {
                    return $col->groupBy('course_id')->map(function (Collection $col) {
                        $first = $col[0];

                        return (object) [
                            'id'               => $first->course_id,
                            'name'             => $first->name,
                            'studyProgramId'   => $first->study_program_id,
                            'semesterRequired' => $first->semester_required,
                            'semesterParity'   => $first->semester_parity,
                            'professorIds'     => $col->pluck('professor_id'),
                        ];
                    });
                });
            });

        $studentCountMap = DB::table('students')
            ->select([
                'study_program_id',
                DB::raw('substr(`id`, 1, 2) as `enrolled_year`'),
                DB::raw('count(*) as `count`'),
            ])
            ->where('status', StudentStatus::Active)
            ->groupBy(['study_program_id', DB::raw('substr(`id`, 1, 2)')])
            ->get()
            ->groupBy(['study_program_id', 'enrolled_year' => fn(stdClass $student) => '20' . $student->enrolled_year])
            ->map->map(fn(Collection $col) => $col->pluck('count')->first());

        $facultyIds = Faculty::query()->pluck('id');

        $studyProgramSlugMap = StudyProgram::query()->pluck('slug', 'id');

        $rooms = DB::query()
            ->from('rooms')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->whereIn('buildings.faculty_id', $facultyIds->unique()->all())
            ->select(['rooms.id', 'rooms.capacity', 'buildings.faculty_id'])
            ->get()
            ->groupBy('faculty_id');

        foreach (Semester::all(['id', 'parity', 'year']) as $semester) {
            $subjects = [];

            foreach ($facultyIds as $facultyId) {
                $spaceAndTimes = [];

                foreach ($rooms[$facultyId] as $room) {
                    foreach (WorkingDay::cases() as $workingDay) {
                        $startTimes = match ($workingDay) {
                            WorkingDay::Friday => ['07:00', '09:30'],
                            default            => ['07:00', '09:30', '13:00', '15:30'],
                        };

                        foreach ($startTimes as $startTime) {
                            $spaceAndTimes[] = [$room, $workingDay, $startTime];
                        }
                    }
                }

                $spaceAndTimes = Arr::shuffle($spaceAndTimes);
                $courses = $courseProfessorMap[$facultyId][$semester->parity->value];
                $professorSchedule = [];

                $spaceAndTimeIndex = 0;

                foreach ($courses as $course) {
                    $studentCount = $studentCountMap[$course->studyProgramId][$semester->year];
                    $joined = 0;

                    for ($i = 0; $joined < $studentCount; $i++) {
                        try {
                            [$room, $workingDay, $startTime] = $spaceAndTimes[$spaceAndTimeIndex++];
                        } catch (Throwable $e) {
                            throw new Exception(sprintf(
                                'Not enough rooms for [%s] faculty.',
                                StudyProgram::find($course->studyProgramId)->name,
                            ), previous: $e);
                        }

                        $joined += $room->capacity;

                        $parallel = chr(65 + $i);
                        $code = '081';
                        $slug = Slug::generate(
                            $studyProgramSlugMap[$course->studyProgramId],
                            $course->name,
                            $semester->parity,
                            $semester->year,
                            $parallel,
                            $code
                        );

                        $day = $workingDay->value;

                        foreach ($course->professorIds as $professorId) {
                            if (isset($professorSchedule[$professorId][$day][$startTime])) {
                                $professorId = null;
                            } else {
                                $professorSchedule[$professorId][$day][$startTime] = true;
                                break;
                            }
                        }

                        if (! isset($professorId)) {
                            continue;
                        }

                        $subjects[] = [
                            'course_id'    => $course->id,
                            'semester_id'  => $semester->id,
                            'professor_id' => $course->professorIds->random(),
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

            Subject::query()->insert($subjects);
        }
    }
}
