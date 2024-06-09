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
            'id'      => function () {
                $year = now()->year;

                return (
                    ($birth = $this->faker->numberBetween($year - 60, $year - 35)) .
                    $this->pad($this->faker->numberBetween(1, 12)) .
                    $this->pad($this->faker->numberBetween(1, 30)) .
                    $birth + $this->faker->numberBetween(26, 35) .
                    $this->pad($this->faker->numberBetween(1, 12)) .
                    $this->pad($this->faker->numberBetween(0, 999), 3)
                );
            },
            'user_id' => fn() => User::factory()->professor(),
            'status'  => $this->faker->randomElement([
                ...array_fill(0, 7, EmployeeStatus::Active), // 70%
                EmployeeStatus::Inactive, // 20%
                EmployeeStatus::Inactive,
                EmployeeStatus::Leave, // 10%
            ]),
        ];
    }

    protected function pad(int $num, int $length = 2): string
    {
        return str_pad($num, $length, 0, STR_PAD_LEFT);
    }
}
