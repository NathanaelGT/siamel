<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Database\Seeders\Datasets\BuildingDataset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (BuildingDataset::get() as $building) {
            Building::factory()
                    ->has(Room::factory()->forEachSequence(
                        ...$building->rooms->toArray()
                    ))
                    ->create($building->toArray());
        }
    }
}
