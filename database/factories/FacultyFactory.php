<?php

namespace Database\Factories;

use App\Enums\Accreditation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacultyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => $this->faker->name(),
            'slug'          => fn(array $attributes) => (string) str($attributes['name'])
                ->replaceMatches('/^fakultas ?/i', '')
                ->slug(language: null, dictionary: ['&' => 'dan']),
            'accreditation' => $this->faker->randomElement(Accreditation::class),
        ];
    }
}
