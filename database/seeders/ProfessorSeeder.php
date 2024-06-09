<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Professor;
use Database\Seeders\Datasets\FacultyDataset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
                $studyProgramCount * 12
            );
        }

        $professorCount['null'] = $this->faker->numberBetween(
            $totalStudyProgramCount * 6,
            $totalStudyProgramCount * 12
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
        foreach ($users as &$user) {
            $definition = $professorFactory->definition();
            $definition['id'] = $definition['id']();
            $definition['user_id'] = $user['id'];
            $definition['faculty_id'] = $facultyId();
            $definition['status'] = $definition['status']->value;

            $user['email'] = $definition['id'] . '@siamel.test';

            $professors[] = $definition;
        }

        DB::table('users')->insert($users);
        DB::table('professors')->insert($professors);
    }
}
