<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->phone_number = normalize_phone_number($user->phone_number);
    }

    public function updating(User $user): void
    {
        if ($user->isDirty('phone_number')) {
            $user->phone_number = normalize_phone_number($user->phone_number);
        }
    }
}
