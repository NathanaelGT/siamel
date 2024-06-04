<?php

namespace App\Models;

use App\Observers\SubmissionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use RuntimeException;

#[ObservedBy(SubmissionObserver::class)]
class Submission extends Model
{
    use HasFactory;

    const CREATED_AT = null;

    protected $fillable = [
        'assignment_id',
        'note',
        'score',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'scored_at'    => 'datetime',
        ];
    }

    public function submissionable(): BelongsTo
    {
        return $this->morphTo();
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function scopeWhereStudent(
        Builder $query, Student | int $student,
        Builder | Subject | int | null $subject = null
    ): void
    {
        if ($subject === null) {
            if (! isset($this->assignment_id)) {
                throw new RuntimeException('Cannot resolve subject');
            }

            $subjectId = Post::query()
                ->select('subject_id')
                ->where('id', $this->assignment_id)
                ->limit(1);
        } else {
            $subjectId = $subject instanceof Subject ? $subject->id : $subject;
        }

        $studentId = $student instanceof Student ? $student->id : $student;

        $query
            ->where(function (Builder $query) use ($studentId) {
                $query
                    ->where('submissionable_type', Student::class)
                    ->where('submissionable_id', $studentId);
            })
            ->orWhere(function (Builder $query) use ($studentId, $subjectId) {
                $query
                    ->where('submissionable_type', SubjectGroup::class)
                    ->where('submissionable_id', SubjectGroup::query()
                        ->select('id')
                        ->where('subject_id', $subjectId)
                        ->whereExists(function (QueryBuilder $query) use ($studentId) {
                            $query->from('subject_group_members')
                                ->whereColumn('subject_group_members.subject_group_id', 'subject_groups.id')
                                ->where('student_id', $studentId);
                        })
                        ->limit(1));
            });
    }

    protected function submitterTitle(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->submissionable_type === SubjectGroup::class) {
                $name = $this->submissionable->name;

                if (str($name)->lower()->startsWith('kelompok')) {
                    return Str::ucfirst($name);
                }

                return "Kelompok $name";
            }

            return $this->submissionable_id . ' - ' . $this->submissionable->account->name;
        });
    }
}
