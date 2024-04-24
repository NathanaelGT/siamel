<?php

namespace App\Models;

use App\Enums\AssignmentCategory;
use App\Enums\AssignmentMime;
use App\Enums\AssignmentType;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'type'     => AssignmentType::class,
            'category' => AssignmentCategory::class,
            'mimes'    => AsEnumCollection::of(AssignmentMime::class),
            'deadline' => 'datetime',
        ];
    }
}
