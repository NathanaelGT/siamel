<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Semester;
use App\Models\User;

class SemesterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function view(User $user, Semester $semester): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function update(User $user, Semester $semester): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function delete(User $user, Semester $semester): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function restore(User $user, Semester $semester): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function forceDelete(User $user, Semester $semester): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }
}
