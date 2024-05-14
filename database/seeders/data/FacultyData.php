<?php

namespace Database\Seeders\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

readonly class FacultyData implements Arrayable
{
    /** @var Collection<int, \Database\Seeders\Data\StudyProgramData> */
    public Collection $studyPrograms;

    /** @var Collection<int, \Database\Seeders\Data\BuildingData> */
    public Collection $buildings;

    public function __construct(
        public int $id,
        public string $name,
        array $studyPrograms,
        array $buildings
    )
    {
        $this->studyPrograms = collect($studyPrograms);
        $this->buildings = collect($buildings);
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
