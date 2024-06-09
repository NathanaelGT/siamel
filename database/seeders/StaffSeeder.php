<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Staff;
use Database\Seeders\Datasets\FacultyDataset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
                $studyProgramCount * 5
            );
        }

        $staffCount['null'] = $this->faker->numberBetween(
            $totalStudyProgramCount * 3,
            $totalStudyProgramCount * 6
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

        $facultyHasAdminMap = [];

        $staff = [];
        $staffFactory = Staff::factory();
        foreach ($users as &$user) {
            $definition = $staffFactory->definition();
            $definition['id'] = $definition['id']();
            $definition['user_id'] = $user['id'];
            $definition['faculty_id'] = $facultyId();
            $definition['status'] = $definition['status']->value;

            if ($facultyHasAdminMap[$definition['faculty_id']] ?? false) {
                if ($this->faker->boolean(10)) {
                    $user['role'] = Role::Admin;
                }
            } else {
                $user['role'] = Role::Admin;
                $facultyHasAdminMap[$definition['faculty_id']] = true;
            }

            $user['email'] = $definition['id'] . '@siamel.test';

            $staff[] = $definition;
        }

        DB::table('users')->insert($users);
        DB::table('staff')->insert($staff);
    }
}
