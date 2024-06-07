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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

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

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(SubjectGroup::class, 'subject_group_members')
            ->using(SubjectGroupMember::class)
            ->whereNull('subject_group_members.deleted_at');
    }

    public function submissions(): MorphMany
    {
        return $this->morphMany(Submission::class, 'submissionable');
    }

    protected function enrolledYear(): Attribute
    {
        return Attribute::get(function (): int {
            return intval('20' . substr($this->id, 0, 2));
        });
    }

    protected function semester(): Attribute
    {
        return Attribute::get(function (): int {
            $currentSemester = Semester::current();

            $semester = ($currentSemester->year - $this->enrolled_year) * 2;
            if ($currentSemester->parity === Parity::Odd) {
                $semester++;
            }

            return $semester;
        });
    }

    protected function semesters(): Attribute
    {
        static $cache = [];

        return Attribute::get(function () use (&$cache): Collection {
            if (! isset($cache[$this->id])) {
                $cache[$this->id] = Semester::query()
                    ->where('year', '>=', $this->enrolled_year)
                    ->orderBy('id', 'desc')
                    ->get();
            }

            return $cache[$this->id];
        });
    }

    protected function semesterLabels(): Attribute
    {
        return Attribute::get(function (): array {
            $enrolledYear = $this->enrolled_year;

            return $this->semesters->mapWithKeys(function (Semester $semester) use ($enrolledYear) {
                $semesterNumber = ($semester->year - $enrolledYear) * 2 + 1;
                if ($semester->parity === Parity::Odd) {
                    $semesterNumber++;
                }

                $label = "$semesterNumber (" . substr($semester->academic_year, 9) . ')';

                return [$semester->id => $label];
            })->all();
        });
    }
}
