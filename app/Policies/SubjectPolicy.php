<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Subject $subject): bool
    {
        return match ($user->role) {
            Role::Admin     => true,
            Role::Staff     => $user->faculty_id === $subject->professor->faculty_id,
            Role::Professor => $user->info_id === $subject->professor_id,
            Role::Student   => $subject->relationLoaded('students')
                ? $subject->students->contains($user->info_id)
                : StudentSubject::query()
                    ->where('student_id', $user->info_id)
                    ->where('subject_id', $subject->id)
                    ->exists(),
        };
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function update(User $user, Subject $subject): bool
    {
        return match ($user->role) {
            Role::Admin     => true,
            Role::Staff     => $user->faculty_id === $subject->professor->faculty_id,
            Role::Professor => $user->info_id === $subject->professor_id,
            default         => false,
        };
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->role === Role::Admin;
    }

    public function restore(User $user, Subject $subject): bool
    {
        return $user->role === Role::Admin;
    }

    public function forceDelete(User $user, Subject $subject): bool
    {
        return $user->role === Role::Admin;
    }
}
