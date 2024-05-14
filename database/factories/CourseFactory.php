<?php

namespace Database\Factories;

use App\Enums\CourseParity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'              => $this->faker->word(),
            'semester_required' => $this->faker->numberBetween(1, 7),
            'semester_parity'   => $this->faker->randomElement(CourseParity::class),
            'is_elective'       => $this->faker->boolean(20),
            'credits'           => $this->faker->numberBetween(2, 3),
        ];
    }

    public function oddSemester(): self
    {
        return $this->state(fn(array $attributes) => [
            'semester_parity' => CourseParity::Odd,
        ]);
    }

    public function evenSemester(): self
    {
        return $this->state(fn(array $attributes) => [
            'semester_parity' => CourseParity::Even,
        ]);
    }

    public function allSemester(): self
    {
        return $this->state(fn(array $attributes) => [
            'semester_parity' => CourseParity::Null,
        ]);
    }
}
