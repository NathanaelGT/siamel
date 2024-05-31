<?php

namespace App\Models;

use App\Enums\WorkingDay;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    ];

    protected function casts(): array
    {
        return [
            'day'        => WorkingDay::class,
            'start_time' => 'datetime',
        ];
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

    public function schedules(): HasMany
    {
        return $this->hasMany(SubjectSchedule::class);
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
