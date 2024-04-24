<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Role;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
            match ($this->role) {
                Role::Admin, Role::Staff => Staff::class,
                Role::Professor          => Professor::class,
                Role::Student            => Student::class,
            },
            'info',
            'role',
            'id',
            'user_id'
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
