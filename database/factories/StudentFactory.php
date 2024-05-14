<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public static int $nextId = 1;

    public function definition(): array
    {
        return [
            'id'               => fn() => static::$nextId++,
            'user_id'          => fn() => User::factory()->student(),
            'study_program_id' => fn() => StudyProgram::factory(),
            'hometown'         => fn() => $this->faker->city(),
            'enrollment_type'  => $this->faker->randomElement([
                'SNMPTN',
                'SBMPTN',
                'Mandiri',
            ]),
            'parent_name'      => $this->faker->name(),
            'parent_phone'     => '08' . match ($this->faker->numberBetween(1, 12)) {
                    1       => sprintf('%09d', mt_rand(1, 999999999)),
                    2, 3    => sprintf('%010d', mt_rand(1, 9999999999)),
                    4, 5, 6 => sprintf('%011d', mt_rand(1, 99999999999)),
                    default => sprintf('%012d', mt_rand(1, 999999999999)),
                },
            'parent_address'   => $this->faker->address(),
            'parent_job'       => $this->faker->jobTitle(),
            'status'           => fn() => $this->faker->randomElement([
                ...array_fill(0, 17, StudentStatus::Active), // 85%
                StudentStatus::Leave, // 10%
                StudentStatus::Leave,
                StudentStatus::DropOut, // 5%
            ]),
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StudentStatus::Active,
        ]);
    }

    public function graduted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StudentStatus::Graduated,
        ]);
    }

    public function dropout(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StudentStatus::DropOut,
        ]);
    }

    public function leave(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => StudentStatus::Leave,
        ]);
    }
}
