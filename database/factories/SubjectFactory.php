<?php

namespace Database\Factories;

use App\Enums\Parity;
use App\Enums\WorkingDay;
use App\Models\Course;
use App\Models\Professor;
use App\Models\Room;
use App\Models\Semester;
use App\Service\Subject\Slug;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id'    => fn() => Course::factory(),
            'semester_id'  => fn() => Semester::factory(),
            'professor_id' => fn() => Professor::factory(),
            'room_id'      => fn() => Room::factory(),
            'capacity'     => 50,
            'slug'         => fn($attribute) => Slug::generate(
                Str::slug($this->faker->paragraph(), language: null, dictionary: []),
                $this->faker->word(),
                $this->faker->randomElement(Parity::class),
                $this->faker->year(),
                $attribute['parallel'],
                $attribute['code']
            ),
            'parallel'     => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E']),
            'code'         => '081',
            'day'          => $this->faker->randomElement(WorkingDay::class),
            'start_time'   => $this->faker->randomElement(['07:00', '09:30', '13:00', '15:30']),
        ];
    }
}
