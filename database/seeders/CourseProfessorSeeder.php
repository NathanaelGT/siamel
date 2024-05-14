<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseProfessor;
use App\Models\Professor;
use App\Models\StudyProgram;
use Illuminate\Support\Collection;

class CourseProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $professors = Professor::query()->get(['id', 'faculty_id']);

        $courses = Course::query()
            ->get(['id', 'study_program_id'])
            ->groupBy('study_program_id')
            ->map(fn(Collection $col) => $col->map->id);

        $studyProgramMap = StudyProgram::query()
            ->whereIn('faculty_id', $professors->pluck('faculty_id')->unique())
            ->get(['id', 'faculty_id'])
            ->groupBy('faculty_id')
            ->map(fn(Collection $col) => $col->map->id);

        $courseProfessor = [];

        $professors->each(function (Professor $professor) use ($courses, $studyProgramMap, &$courseProfessor) {
            collect($studyProgramMap->get($professor->faculty_id))
                ->flatMap(fn(int $studyProgramId) => $courses[$studyProgramId])
                ->whenNotEmpty(function (Collection $options) use ($professor, &$courseProfessor) {
                    $options->random($this->faker->numberBetween(2, $options->count() / 3))
                        ->each(function (int $courseId) use ($professor, &$courseProfessor) {
                            $courseProfessor[] = [
                                'course_id'    => $courseId,
                                'professor_id' => $professor->id,
                            ];
                        });
                });
        });

        CourseProfessor::query()->insert($courseProfessor);
    }
}
