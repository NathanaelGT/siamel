<?php

namespace Database\Seeders\Data;

readonly class RoomsData
{
    public function __construct(
        public int $floor,
        public int $roomCount,
        public int $capacity = 50,
    )
    {
    }
}
