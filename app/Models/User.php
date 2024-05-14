<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Role;
use App\Observers\UserObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read Student|Professor|Staff $info
 */
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail, CanResetPassword
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'avatar_url',
        'phone_number',
        'gender',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'gender'            => Gender::class,
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => Role::class,
        ];
    }

    public function info(): MorphTo
    {
        return $this->morphInstanceTo(
            $this->role->model(),
            'info',
            'role',
            'id',
            'user_id'
        );
    }

    public function facultyId(): Attribute
    {
        return new Attribute(
            get: function (): ?int {
                if ($this->role === Role::Student) {
                    return $this->info->study_program->faculty_id;
                }

                return $this->info->faculty_id;
            },
        );
    }

    public function panelId(): string
    {
        return match ($this->role) {
            Role::Admin, Role::Staff => 'staff',
            Role::Professor          => 'professor',
            Role::Student            => 'student',
        };
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === $this->panelId();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }
}
