<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\StudentStatus;
use App\Jobs\Seeder\StudentSeederJob;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\Data\FacultyData;
use Database\Seeders\Datasets\FacultyDataset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(
        ?FacultyData $faculty = null,
        ?int $year = null,
        array $userIds = null,
        array $userCounts = null
    ): void
    {
        if ($faculty === null) {
            $lastUserId = User::query()->count();

            Semester::query()
                ->distinct()
                ->pluck('year')
                ->each(function (string $year) use (&$lastUserId) {
                    foreach (FacultyDataset::get()->shuffle() as $faculty) {
                        $userCounts = $faculty->studyPrograms->map(function () {
                            return $this->faker->numberBetween(150, 400);
                        });
                        $userCount = $userCounts->sum();

                        $userIds = range($start = $lastUserId + 1, $lastUserId = $start + $userCount);

                        StudentSeederJob::dispatch($faculty, (int) $year, $userIds, $userCounts->all());
                    }
                });

            Artisan::call('queue:work --stop-when-empty');

            return;
        }

        $currentYear = now()->year;

        $pad = function (int $num, int $length = 2): string {
            return str_pad($num, $length, 0, STR_PAD_LEFT);
        };

        $recentStudentStatuses = [
            ...array_fill(0, 17, StudentStatus::Active->value), // 85%
            StudentStatus::Leave->value, // 10%
            StudentStatus::Leave->value,
            StudentStatus::DropOut->value, // 5%
        ];

        $oldStudentStatuses = [
            ...array_fill(0, 14, StudentStatus::Graduated->value), // 70%
            ...array_fill(0, 4, StudentStatus::Active->value), // 20%
            StudentStatus::Leave->value, // 5%
            StudentStatus::DropOut->value, // 5%
        ];

        $ancientStudentStatuses = [
            ...array_fill(0, 19, StudentStatus::Graduated->value), // 95%
            StudentStatus::Active->value, // 5%
        ];

        $rememberToken = Str::random(10);

        for ($i = 0; $i < count($userCounts); $i++) {
            $userCounts[$i] += $i === 0 ? 0 : $userCounts[$i - 1];
        }

        $y = substr($year, -2);
        $facultyId = $pad($faculty->id);

        $userFactory = User::factory();
        $studentFactory = Student::factory();
        $users = [];
        $students = [];

        $idx = 0;
        $now = now()->toDateTimeString();

        foreach ($faculty->studyPrograms as $i => $studyProgram) {
            $id = 0;
            $level = $pad($studyProgram->level->getId());

            for (; $idx < $userCounts[$i]; $idx++) {
                $user = $userFactory->definition();
                $user['id'] = $userIds[$idx];
                $user['role'] = Role::Student->name;
                $user['remember_token'] = $rememberToken;
                $user['email_verified_at'] = $now;
                $user['created_at'] = $now;
                $user['updated_at'] = $now;
                $user['name'] = $user['name']($user);
                $user['email'] = (string) str(
                    str($user['name'])
                        ->explode(' ')
                        ->when($this->faker->boolean(35))->shuffle()
                        ->push($user['id'])
                        ->when($this->faker->boolean(65), function (Collection $email) {
                            $email->push($this->faker->year());
                        })
                        ->join(' ')
                )
                    ->snake($this->faker->randomElement(['_', '-', '.', '']))
                    ->append('@' . $this->faker->freeEmailDomain());

                $users[] = $user;

                $student = $studentFactory->definition();
                $student['user_id'] = $userIds[$idx];
                $student['study_program_id'] = $studyProgram->id;
                $student['hometown'] = $this->faker->city();
                $student['id'] = (int) implode([
                    $y,
                    $facultyId,
                    $studyProgram->relative_id, // memang engga perlu dipad
                    $level,
                    $pad(++$id, 4),
                ]);
                $student['status'] = match (true) {
                    $currentYear - $year < 4 => $this->faker->randomElement($recentStudentStatuses),
                    $currentYear - $year < 5 => $this->faker->randomElement($oldStudentStatuses),
                    $currentYear - $year < 6 => $this->faker->randomElement($ancientStudentStatuses),
                    default                  => StudentStatus::Graduated->value,
                };

                $students[] = $student;
            }
        }

        User::query()->insert($users);
        Student::query()->insert($students);
    }
}
