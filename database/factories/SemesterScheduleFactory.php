<?php

namespace Database\Factories;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SemesterSchedule>
 */
class SemesterScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'semester_id' => fn() => Semester::factory(),
            'name'        => implode(' ', $this->faker->words()),
            'start_date'  => $this->faker->date(),
            'end_date'    => fn(array $attributes) => $this->faker
                ->dateTimeBetween($attributes['start_date'])
                ->format('Y-m-d'),
        ];
    }
}
