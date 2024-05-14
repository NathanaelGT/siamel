<?php

namespace Database\Seeders\Datasets;

use Database\Seeders\Data\BuildingData;
use Database\Seeders\Data\RoomData;
use Database\Seeders\Data\RoomsData;
use Illuminate\Support\Collection;

abstract class BuildingDataset
{
    /**
     * @return Collection<BuildingData>
     */
    public static function get(): Collection
    {
        return once(fn() => collect([
            new BuildingData('Gedung Kuliah Bersama', RoomData::mass([
                new RoomsData(1, 2),
                new RoomData('Lab 1'),
                new RoomData('Lab 2'),
                new RoomsData(2, 4),
                new RoomsData(3, 4),
                new RoomsData(4, 4),
                new RoomsData(5, 6),
                new RoomsData(6, 6),
                new RoomsData(7, 6),
            ])),
        ]));
    }
}
