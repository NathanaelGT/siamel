<?php

namespace Database\Seeders\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class BuildingData implements Arrayable
{
    /** @var Collection<int, \Database\Seeders\Data\RoomData> */
    public readonly Collection $rooms;

    public function __construct(
        public readonly string $name,
        array $rooms,
    )
    {
        $this->rooms = collect($rooms);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
