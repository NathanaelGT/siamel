<?php

namespace Database\Factories;

use App\Enums\Parity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Semester>
 */
class SemesterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parity' => $this->faker->randomElement(Parity::class),
            'year'   => $this->faker->year(),
        ];
    }
}
