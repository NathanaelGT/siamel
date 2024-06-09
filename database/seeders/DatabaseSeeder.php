<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        ini_set('memory_limit', '2048M');

        if (false) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            DB::table('failed_jobs')->truncate();
            DB::table('jobs')->truncate();
//            DB::table('attendances')->truncate();
            DB::table('attachments')->truncate();
            DB::table('submissions')->truncate();
//            DB::table('assignments')->truncate();
//            DB::table('subject_group_members')->truncate();
//            DB::table('subject_groups')->truncate();
//            DB::table('posts')->truncate();
//            DB::table('subject_schedules')->truncate();
//            DB::table('student_subject')->truncate();
//            DB::table('subjects')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

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
