<?php

namespace App\Models;

use App\Enums\CourseParity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'study_program_id',
        'semester_required',
        'semester_parity',
        'is_elective',
        'credits',
    ];

    protected function casts(): array
    {
        return [
            'semester_parity' => CourseParity::class,
            'is_elective'     => 'bool',
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
