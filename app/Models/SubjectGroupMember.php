<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectGroupMember extends Pivot
{
    use SoftDeletes;

    protected $table = 'subject_group_members';

    public $incrementing = true;

    public $timestamps = false;

    public function group(): BelongsTo
    {
        return $this->belongsTo(SubjectGroup::class, 'subject_group_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
