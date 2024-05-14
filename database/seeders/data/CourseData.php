<?php

namespace Database\Seeders\Data;

use App\Enums\Parity;
use Illuminate\Contracts\Support\Arrayable;

readonly class CourseData implements Arrayable
{
    public Parity $semester_parity;

    public function __construct(
        public string $name,
        public int $semester_required,
        public int $credits = 3,
        Parity $semester_parity = null,
    )
    {
        $this->semester_parity = $semester_parity ?? ($this->semester_required % 2 ? Parity::Odd : Parity::Even);
    }

    public function toArray(): array
    {
        return [
            'name'              => $this->name,
            'semester_required' => $this->semester_required,
            'semester_parity'   => $this->semester_parity->value,
            'is_elective'       => false,
            'credits'           => $this->credits,
        ];
    }
}
