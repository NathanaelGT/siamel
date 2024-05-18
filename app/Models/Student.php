<?php

namespace App\Models;

use App\Enums\Parity;
use App\Enums\StudentStatus;
use App\Observers\StudentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function faculty(): HasOneThrough
    {
        return $this->hasOneThrough(Faculty::class, StudyProgram::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->using(StudentSubject::class);
    }

    public function currentSemesterSubjects(): BelongsToMany
    {
        return $this->subjects()->where('semester_id', Semester::current()->id);
    }

    public function semester(): Attribute
    {
        return Attribute::get(function (): int {
            $currentSemester = Semester::current();
            $studentEntryYear = intval('20' . substr($this->id, 0, 2));

            $semester = ($currentSemester->year - $studentEntryYear) * 2;
            if ($currentSemester->parity === Parity::Odd) {
                $semester--;
            }

            return $semester;
        });
    }
}
