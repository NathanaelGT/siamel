<?php

namespace App\Models;

use App\Enums\PostType;
use App\Enums\WorkingDay;
use App\Notifications\SyncDatabaseNotification;
use Carbon\CarbonPeriod;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;

class Subject extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'semester_id',
        'professor_id',
        'room_id',
        'capacity',
        'parallel',
        'code',
        'slug',
        'note',
        'day',
        'start_time',
        'student_can_manage_group',
        'student_can_create_group',
        'group_max_members',
    ];

    protected function casts(): array
    {
        return [
            'day'                      => WorkingDay::class,
            'start_time'               => 'datetime',
            'student_can_manage_group' => 'bool',
            'student_can_create_group' => 'bool',
        ];
    }

    public function notifyStudents(Notification $notification): void
    {
        $users = (new User)->hydrate(
            $this->students()
                ->pluck('user_id')
                ->map(fn($id) => ['id' => $id])
                ->all()
        );

        $users->each->notify(
            new SyncDatabaseNotification($notification->getDatabaseMessage())
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->using(StudentSubject::class)
            ->withPivot('registered_at');
    }

    public function studentSubjects(): HasMany
    {
        return $this->hasMany(StudentSubject::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SubjectSchedule::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function submissions(): HasManyThrough
    {
        return $this->hasManyThrough(Submission::class, Post::class, secondKey: 'assignment_id')
            ->where('type', PostType::Assignment);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(SubjectGroup::class);
    }

    public function groupMembers(): HasManyThrough
    {
        return $this->hasManyThrough(SubjectGroupMember::class, SubjectGroup::class);
    }

    public function scopeWhereStudent(Builder $query, Student | int $student): void
    {
        $studentId = $student instanceof Student ? $student->id : $student;

        if ($query->getSelect() === null) {
            $query->select(['subjects.*']);
        }

        $query->join('student_subject', function (JoinClause $query) use ($studentId) {
            $query->on('subjects.id', '=', 'student_subject.subject_id')
                ->where('student_id', $studentId);
        });
    }

    protected function title(): Attribute
    {
        return Attribute::get(function (): string {
            return ($this->course_name ?? $this->course->name) . ' ' . $this->parallel . $this->code;
        });
    }

    protected function endTime(): Attribute
    {
        return Attribute::get(function (): Carbon {
            $credits = $this->course->credits ?? $this->course_credits;

            return $this->start_time->clone()->addMinutes($credits * 50);
        });
    }

    protected function timePeriod(): Attribute
    {
        return Attribute::get(fn(): CarbonPeriod => CarbonPeriod::create($this->start_time, $this->end_time));
    }

    protected function time(): Attribute
    {
        return Attribute::get(fn(): string => implode(' - ', [
            $this->start_time->format('H:i'),
            $this->end_time->format('H:i'),
        ]));
    }
}
