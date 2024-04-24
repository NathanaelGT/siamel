<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasAccount
{
    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
