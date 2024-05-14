<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 */
class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => $this->faker->unique()->domainName() . ' ' . $this->faker->numberBetween(1, 4),
            'abbreviation' => fn(array $attributes) => abbreviation($attributes['name']),
            'faculty_id'   => null,
        ];
    }
}
