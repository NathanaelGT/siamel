<?php

namespace App\Livewire\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\PasswordResetResponse;
use Filament\Pages\Auth\PasswordReset\ResetPassword as FilamentResetPassword;

class ResetPassword extends FilamentResetPassword
{
    public function mount(?string $email = null, ?string $token = null): void
    {
        /** @var ?\App\Models\User $user */
        if ($user = Filament::auth()->user()) {
            $panel = Filament::getPanel($user->panelId());

            $this->redirectIntended($panel->getUrl());

            return;
        }

        $this->email = $email ?? request()->query('email');
        $this->token = $token ?? request()->query('token');

//        $trueResetPath = route('password.reset');
//        if (request()->fullUrl() !== $trueResetPath) {
//            $this->redirect($trueResetPath);
//
//            return;
//        }

        $this->form->fill([
            'email' => $this->email,
        ]);
    }

    public function resetPassword(): ?PasswordResetResponse
    {
        $result = parent::resetPassword();

        if ($result !== null) {
            session()->flash('email', $this->email);
        }

        return $result;
    }
}
