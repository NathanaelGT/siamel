<?php

namespace App\Models;

use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Student extends Model implements Contracts\HasAccountContract
{
    use Concerns\HasAccount, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'hometown',
        'enrollment_type',
        'parent_name',
        'parent_phone',
        'parent_address',
        'parent_job',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => StudentStatus::class,
        ];
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function faculty(): HasOneThrough
    {
        return $this->hasOneThrough(Faculty::class, StudyProgram::class);
    }
}
