<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemesterSchedule extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'semester_id',
        'name',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
