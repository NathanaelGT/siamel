<?php

namespace Database\Seeders\Data;

use App\Enums\EducationLevel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StudyProgramData implements Arrayable
{
    public static int $idCounter = 1;

    public readonly int $id;

    public readonly string $slug;

    /** @var Collection<int, \Database\Seeders\Data\CourseData> */
    public Collection $courses;

    public function __construct(
        public readonly int $relative_id,
        public readonly string $name,
        public readonly EducationLevel $level,
        array $courses,
    )
    {
        $this->id = static::$idCounter++;
        $this->slug = Str::slug($name, language: null, dictionary: []);
        $this->courses = collect($courses);
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'relative_id' => $this->relative_id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'level'       => $this->level->value,
        ];
    }
}
