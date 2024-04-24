<?php

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface HasAccountContract
{
    public function account(): BelongsTo;
}
