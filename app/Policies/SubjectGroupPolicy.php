<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Subject;
use App\Models\SubjectGroup;
use App\Models\User;

class SubjectGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SubjectGroup $group): bool
    {
        return match ($user->role) {
            Role::Admin     => true,
            Role::Staff     => false,
            Role::Professor => $group->subject->professor_id === $user->info_id,
            Role::Student   => $group->members()->where('student_id', $user->info_id)->exists(),
        };
    }

    public function create(User $user, Subject $subject): bool
    {
        return match ($user->role) {
            Role::Admin     => true,
            Role::Staff     => false,
            Role::Professor => $subject->professor_id === $user->info_id,
            Role::Student   => $subject->students()->where('student_id', $user->info_id)->exists(), // REVIEW
        };
    }

    public function update(User $user, SubjectGroup $group): bool
    {
        return match ($user->role) {
            Role::Admin     => true,
            Role::Staff     => false,
            Role::Professor => $group->subject->professor_id === $user->info_id,
            Role::Student   => $group->members()->where('student_id', $user->info_id)->exists(), // REVIEW
        };
    }

    public function delete(User $user, SubjectGroup $group): bool
    {
        return match ($user->role) {
            Role::Staff, Role::Student => false,
            default                    => true,
        };
    }

    public function restore(User $user, SubjectGroup $group): bool
    {
        return match ($user->role) {
            Role::Staff, Role::Student => false,
            default                    => true,
        };
    }

    public function forceDelete(User $user, SubjectGroup $group): bool
    {
        return match ($user->role) {
            Role::Staff, Role::Student => false,
            default                    => true,
        };
    }
}
