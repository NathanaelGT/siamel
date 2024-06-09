<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'      => function () {
                $year = now()->year;

                return (
                    ($birth = $this->faker->numberBetween($year - 60, $year - 27)) .
                    $this->pad($this->faker->numberBetween(1, 12)) .
                    $this->pad($this->faker->numberBetween(1, 30)) .
                    $birth + $this->faker->numberBetween(23, 27) .
                    $this->pad($this->faker->numberBetween(1, 12)) .
                    $this->pad($this->faker->numberBetween(0, 999), 3)
                );
            },
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

    protected function pad(int $num, int $length = 2): string
    {
        return str_pad($num, $length, 0, STR_PAD_LEFT);
    }
}
