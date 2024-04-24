<?php

namespace App\Models;

use App\Enums\Parity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'study_program_id',
        'semester_required',
        'semester_parity',
        'credits',
    ];

    protected function casts(): array
    {
        return [
            'semester_parity' => Parity::class,
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }
}
