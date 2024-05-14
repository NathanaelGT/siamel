<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfessorFactory extends Factory
{
    public static int $nextId = 1;

    public function definition(): array
    {
        return [
            'id'      => static::$nextId++,
            'user_id' => fn() => User::factory()->professor(),
            'status'  => $this->faker->randomElement([
                ...array_fill(0, 7, EmployeeStatus::Active), // 70%
                EmployeeStatus::Inactive, // 20%
                EmployeeStatus::Inactive,
                EmployeeStatus::Leave, // 10%
            ]),
        ];
    }
}
