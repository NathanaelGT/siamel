<?php

namespace App\Service\Auth;

use App\Enums\Role;
use App\Models\Professor;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Filament\Events\Auth\Registered;
use Illuminate\Support\Arr;

abstract class CreateAccount
{
    public static function admin(array $data): Staff
    {
        return static::create(Role::Admin, $data);
    }

    public static function staff(array $data): Staff
    {
        return static::create(Role::Staff, $data);
    }

    public static function professor(array $data): Professor
    {
        return static::create(Role::Professor, $data);
    }

    public static function student(array $data): Student
    {
        $data['account']['parent_phone'] = normalize_phone_number($data['account']['parent_phone'] ?? '');

        return static::create(Role::Student, $data);
    }

    protected static function create(Role $role, array $data): Staff | Professor | Student
    {
        $data = Arr::undot($data);
        $data['status'] = 'Aktif';

        $userData = Arr::pull($data, 'account');
        $userData['role'] = $role;
        $userData['phone_number'] = normalize_phone_number($userData['phone_number'] ?? '');

        $account = User::create($userData);
        $userable = $role->model()::create($data + ['user_id' => $account->id]);

        event(new Registered($account));

        return $userable;
    }
}
