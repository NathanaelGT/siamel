<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Professor;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SemesterSeeder::class,
            FacultySeeder::class,
            BuildingSeeder::class,
            StaffSeeder::class,
            ProfessorSeeder::class,
            CourseProfessorSeeder::class,
            SubjectSeeder::class,
        ]);

        Staff::factory()
            ->state([
                'id'      => 999999,
                // 'faculty_id' => 8,
                'user_id' => User::factory()->state([
                    'name'  => 'Admin',
                    'email' => 'admin@gmail.com',
                    'role'  => Role::Admin,
                ]),
            ])
            ->create();

        Staff::factory()
            ->state([
                'id'      => 99999,
                // 'faculty_id' => 8,
                'user_id' => User::factory()->state([
                    'name'  => 'Staff',
                    'email' => 'staff@gmail.com',
                    'role'  => Role::Staff,
                ]),
            ])
            ->create();

        Professor::factory()
            ->state([
                'id'         => 99999,
                'faculty_id' => 8,
                'user_id'    => User::factory()->state([
                    'name'  => 'Dosen',
                    'email' => 'dosen@gmail.com',
                    'role'  => Role::Professor,
                ]),
            ])
            ->create();

        Student::factory()
            ->state([
                'id'               => 99999,
                'study_program_id' => 25,
                'user_id'          => User::factory()->state([
                    'name'  => 'Mahasiswa',
                    'email' => 'mahasiswa@gmail.com',
                    'role'  => Role::Student,
                ]),
            ])
            ->create();

        $this->callWithoutContainer([
            StudentSeeder::class,
        ]);
    }
}
