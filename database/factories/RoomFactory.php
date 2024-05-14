<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => implode([
                'Ruangan ',
                $this->faker->numberBetween(1, 4),
                '0',
                $this->faker->numberBetween(1, 8),
            ]),
            'capacity'    => 50,
            'building_id' => Building::factory(),
        ];
    }
}
