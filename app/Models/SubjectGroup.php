<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectGroup extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
