<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

trait HasAccount
{
    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updateData($data): bool
    {
        $accountData = Arr::pull($data, 'account');

        $successfullyUpdateThis = $this->update($data);
        $successfullyUpdateAccount = ! empty($accountData) && $this->account()->update($accountData);

        return $successfullyUpdateThis && $successfullyUpdateAccount;
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn(): string => $this->account->name);
    }
}
