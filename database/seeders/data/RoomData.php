<?php

namespace Database\Seeders\Data;

use Illuminate\Contracts\Support\Arrayable;

class RoomData implements Arrayable
{
    public function __construct(
        public readonly string $name,
        public readonly int $capacity = 50
    )
    {
    }

    /** @return static[] */
    public static function mass(array $data): array
    {
        return collect($data)
            ->flatMap(function (RoomsData | RoomData $room) {
                if ($room instanceof RoomData) {
                    return [$room];
                }

                $rooms = [];

                for ($no = 1; $no <= $room->roomCount; $no++) {
                    $rooms[] = new RoomData('Ruangan ' . $room->floor . str_pad($no, 2, 0, STR_PAD_LEFT));
                }

                return $rooms;
            })
            ->all();
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
