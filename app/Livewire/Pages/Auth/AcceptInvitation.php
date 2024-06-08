<?php

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use App\Service\Auth\Invitation;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;

/**
 * @property \Filament\Forms\Form $form
 */
class AcceptInvitation extends SimplePage
{
    use InteractsWithFormActions;
    use CanUseDatabaseTransactions;

    protected static string $view = 'livewire.pages.auth.accept-invitation';

    #[Locked]
    public string $uid;

    public array $data;

    public function mount(string $userId, string $hash): void
    {
        /** @var ?\App\Models\User $user */
        if ($user = Filament::auth()->user()) {
            $panel = Filament::getPanel($user->panelId());

            redirect()->intended($panel->getUrl());

            return;
        }

        $this->uid = $userId;

        $id = Invitation::decodeId($userId);
        $user = User::query()
            ->whereNull('email_verified_at')
            ->find($id, ['name', 'gender', 'phone_number', 'email', 'created_at']);

        if ($user && hash_equals(Invitation::hashUser($user), $hash)) {
            $this->data = Arr::except($user->getAttributes(), 'created_at');
        } else {
            $this->redirectIntended(Filament::getUrl());
        }
    }

    public function register(): ?RegistrationResponse
    {
        $id = Invitation::decodeId($this->uid);
        $data = $this->form->getState();

        $user = (new User)->hydrate([['id' => $id]])
            ->first()
            ->forceFill([
                'email_verified_at' => now(),
            ]);

        $this->wrapInDatabaseTransaction(function () use ($user, $data) {
            $user->update($data);

            event(new Verified($user));
        });

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function getForms(): array
    {
        return [
            'form' => Form::make($this)
                ->model(User::class)
                ->statePath('data')
                ->columns(['sm' => 2])
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('gender')
                        ->disabled(),

                    Forms\Components\TextInput::make('phone_number')
                        ->tel()
                        ->required()
                        ->maxLength(16),

                    Forms\Components\TextInput::make('email')
                        ->disabled(),

                    Forms\Components\TextInput::make('password')
                        ->label(__('filament-panels::pages/auth/register.form.password.label'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required()
                        ->rule(Password::default())
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->same('passwordConfirmation')
                        ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),

                    Forms\Components\TextInput::make('passwordConfirmation')
                        ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required()
                        ->dehydrated(false),
                ]),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('register')
                ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
                ->submit('register'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getMaxWidth(): MaxWidth | string | null
    {
        return MaxWidth::ThreeExtraLarge;
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-panels::pages/auth/register.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('filament-panels::pages/auth/register.heading');
    }
}
