<?php

namespace Database\Seeders;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '2048M');

        $this->call([
            SemesterSeeder::class,
            FacultySeeder::class,
            BuildingSeeder::class,
            StaffSeeder::class,
            ProfessorSeeder::class,
            CourseProfessorSeeder::class,
            AccessibleEmployeeSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            StudentSubjectSeeder::class,
            SubjectScheduleSeeder::class,
            SubjectGroupSeeder::class,
            PostSeeder::class,
            SubmissionSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}
