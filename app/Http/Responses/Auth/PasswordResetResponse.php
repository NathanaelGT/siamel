<?php

namespace App\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\PasswordResetResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class PasswordResetResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->route('login');
    }
}
