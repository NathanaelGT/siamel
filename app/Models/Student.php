<?php

namespace App\Models;

use App\Enums\StudentStatus;
use App\Observers\StudentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[ObservedBy(StudentObserver::class)]
class Student extends Model implements Contracts\HasAccountContract
{
    use Concerns\HasAccount, HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'study_program_id',
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
