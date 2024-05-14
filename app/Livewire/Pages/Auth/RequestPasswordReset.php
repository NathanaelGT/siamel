<?php

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as FilamentRequestPasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class RequestPasswordReset extends FilamentRequestPasswordReset
{
    public function mount(): void
    {
        /** @var ?\App\Models\User $user */
        if ($user = Filament::auth()->user()) {
            $panel = Filament::getPanel($user->panelId());

            $this->redirectIntended($panel->getUrl());

            return;
        }

        $trueRequestPath = route('password.request');
        if (request()->fullUrl() !== $trueRequestPath) {
            $this->redirect($trueRequestPath);

            return;
        }

        $this->form->fill();
    }

    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        $status = Password::broker(Filament::getAuthPasswordBroker())
            ->sendResetLink($data, function (User $user, string $token) {
                $notification = new ResetPasswordNotification($token);
                $notification->url = URL::temporarySignedRoute('password.reset', now()->addHour(), [
                    'email' => $user->getEmailForPasswordReset(),
                    'token' => $token,
                ]);

                $user->notify($notification);
            });

        if ($status !== Password::RESET_LINK_SENT) {
            Notification::make()
                ->title(__($status))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__($status))
            ->success()
            ->send();

        $this->form->fill();
    }

    public function loginAction(): Action
    {
        return parent::loginAction()->url(route('login'));
    }
}
