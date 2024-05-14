<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Professor;
use Database\Seeders\Datasets\FacultyDataset;
use Illuminate\Support\Arr;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $totalStudyProgramCount = 0;

        $professorCount = [];

        foreach (FacultyDataset::get() as $faculty) {
            $studyProgramCount = $faculty->studyPrograms->count();
            $totalStudyProgramCount += $studyProgramCount;

            $professorCount[$faculty->id] = $this->faker->numberBetween(
                $studyProgramCount * 6,
                $studyProgramCount * 11
            );
        }

        $professorCount['null'] = $this->faker->numberBetween(
            $totalStudyProgramCount * 6,
            $totalStudyProgramCount * 11
        );

        $users = $this->generateUsers(collect($professorCount)->sum(), Role::Professor);

        $facultyId = function () use (&$professorCount) {
            foreach ($professorCount as $facultyId => &$count) {
                if (! --$count) {
                    Arr::forget($professorCount, $facultyId);
                };

                return $facultyId === 'null' ? null : $facultyId;
            }

            return null;
        };

        $professors = [];
        $professorFactory = Professor::factory();
        foreach ($users as $user) {
            $definition = $professorFactory->definition();
            $definition['user_id'] = $user['id'];
            $definition['faculty_id'] = $facultyId();
            $definition['status'] = $definition['status']->value;

            $professors[] = $definition;
        }

        Professor::query()->insert($professors);
    }
}
