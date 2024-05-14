<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var \App\Models\User $user */
        $user = $guard->user();
        $panelId = $user->panelId();

        if (Filament::getCurrentPanel()->getId() !== $panelId) {
            redirect()->route("filament.$panelId.pages.dashboard")->send();
        }
    }
}
