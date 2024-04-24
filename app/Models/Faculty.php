<?php

namespace App\Models;

use App\Enums\Accreditation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'accreditation',
    ];

    protected function casts(): array
    {
        return [
            'accreditation' => Accreditation::class,
        ];
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
