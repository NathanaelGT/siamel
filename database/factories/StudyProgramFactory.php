<?php

namespace Database\Factories;

use App\Enums\EducationLevel;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudyProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'relative_id' => fn() => $this->faker->numberBetween(1, 1e9),
            'name'        => fn() => implode(' ', $this->faker->words(2)),
            'slug'        => fn(array $attributes) => Str::slug(value($attributes['name']), language: null, dictionary: []),
            'faculty_id'  => fn() => Faculty::factory(),
            'level'       => $this->faker->randomElement(EducationLevel::class),
        ];
    }
}
