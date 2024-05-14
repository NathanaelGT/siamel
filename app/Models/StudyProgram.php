<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Observers\StudyProgramObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(StudyProgramObserver::class)]
class StudyProgram extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'slug',
        'faculty_id',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'level' => EducationLevel::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
