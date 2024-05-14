<?php

namespace App\Livewire\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as FilamentLogin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends FilamentLogin
{
    public function mount(): void
    {
        /** @var ?\App\Models\User $user */
        if ($user = Filament::auth()->user()) {
            $panel = Filament::getPanel($user->panelId());

            $this->redirectIntended($panel->getUrl());

            return;
        }

        $trueLoginPath = route('login');
        if (request()->fullUrl() !== $trueLoginPath) {
            $this->redirect($trueLoginPath);

            return;
        }

        $this->form->fill();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        /** @var \App\Models\User $user */
        $user = Filament::auth()->user();

        $panel = Filament::getPanel($user->panelId());

        Filament::setCurrentPanel($panel);

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getEmailFormComponent(): Component
    {
        $email = session()->get('email');

        return parent::getEmailFormComponent()
            ->default($email)
            ->autofocus($email === null);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->autofocus(session()->has('email'))
            ->hint(
                new HtmlString(Blade::render('<x-filament::link :href="route(\'password.request\')"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>'))
            );
    }
}
