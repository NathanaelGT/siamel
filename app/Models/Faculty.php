<?php

namespace App\Models;

use App\Enums\Accreditation;
use App\Observers\FacultyObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(FacultyObserver::class)]
class Faculty extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'slug',
        'accreditation',
    ];

    protected function casts(): array
    {
        return [
            'accreditation' => Accreditation::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function studyPrograms(): HasMany
    {
        return $this->hasMany(StudyProgram::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }
}
