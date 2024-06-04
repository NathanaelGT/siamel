<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SubjectGroup extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'subject_id',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'subject_group_members');
    }

    public function submissions(): MorphMany
    {
        return $this->morphMany(Submission::class, 'submissionable');
    }
}
