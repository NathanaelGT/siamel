<?php

namespace App\Listeners;

use App\Notifications\UserInvitation;
use Illuminate\Auth\Events\Registered;

class SendUserInvitation
{
    public function handle(Registered $event): void
    {
        $event->user->notify(new UserInvitation);
    }
}
