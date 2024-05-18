<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Role;
use App\Exceptions\InvalidRoleException;
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

    public function infoId(): Attribute
    {
        static $cache = [];

        return Attribute::get(function () use (&$cache): int {
            return $cache[$this->id] ??= $this->relationLoaded('info')
                ? $this->info->id
                : $this->info()->value('id');
        });
    }

    public function student(): Attribute
    {
        return Attribute::get(function (): Student {
            if ($this->info instanceof Student) {
                return $this->info;
            }

            throw new InvalidRoleException(expect: Role::Student, actual: $this->role);
        });
    }

    public function professor(): Attribute
    {
        return Attribute::get(function (): Professor {
            if ($this->info instanceof Professor) {
                return $this->info;
            }

            throw new InvalidRoleException(expect: Role::Professor, actual: $this->role);
        });
    }

    public function staff(): Attribute
    {
        return Attribute::get(function (): Staff {
            if ($this->info instanceof Staff) {
                return $this->info;
            }

            throw new InvalidRoleException(expect: Role::Staff, actual: $this->role);
        });
    }

    public function admin(): Attribute
    {
        return Attribute::get(function (): Staff {
            if ($this->info instanceof Staff) {
                return $this->info;
            }

            throw new InvalidRoleException(expect: Role::Admin, actual: $this->role);
        });
    }

    public function facultyId(): Attribute
    {
        return Attribute::get(function (): ?int {
            if ($this->role === Role::Student) {
                return $this->info->studyProgram->faculty_id;
            }

            return $this->info->faculty_id;
        });
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
