<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    public static int $nextId = 1;

    public function definition(): array
    {
        return [
            'id'      => static::$nextId++,
            'user_id' => fn() => User::factory()->staff(),
            'status'  => $this->faker->randomElement([
                ...array_fill(0, 7, EmployeeStatus::Active), // 70%
                EmployeeStatus::Inactive, // 20%
                EmployeeStatus::Inactive,
                EmployeeStatus::Leave, // 10%
            ]),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => fn() => User::factory()->admin(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => EmployeeStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => EmployeeStatus::Inactive,
        ]);
    }

    public function leave(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => EmployeeStatus::Leave,
        ]);
    }
}
