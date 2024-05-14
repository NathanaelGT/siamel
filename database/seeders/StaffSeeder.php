<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Staff;
use Database\Seeders\Datasets\FacultyDataset;
use Illuminate\Support\Arr;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $totalStudyProgramCount = 0;

        $staffCount = [];

        foreach (FacultyDataset::get() as $faculty) {
            $studyProgramCount = $faculty->studyPrograms->count();
            $totalStudyProgramCount += $studyProgramCount;

            $staffCount[$faculty->id] = $this->faker->numberBetween(
                $studyProgramCount * 2,
                $studyProgramCount * 4
            );
        }

        $staffCount['null'] = $this->faker->numberBetween(
            $totalStudyProgramCount * 3,
            $totalStudyProgramCount * 5
        );

        $users = $this->generateUsers(collect($staffCount)->sum(), Role::Staff);

        $facultyId = function () use (&$staffCount) {
            foreach ($staffCount as $facultyId => &$count) {
                if (! --$count) {
                    Arr::forget($staffCount, $facultyId);
                };

                return $facultyId === 'null' ? null : $facultyId;
            }

            return null;
        };

        $staff = [];
        $staffFactory = Staff::factory();
        foreach ($users as $user) {
            $definition = $staffFactory->definition();
            $definition['user_id'] = $user['id'];
            $definition['faculty_id'] = $facultyId();
            $definition['status'] = $definition['status']->value;

            $staff[] = $definition;
        }

        Staff::query()->insert($staff);
    }
}
