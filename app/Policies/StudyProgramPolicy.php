<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\StudyProgram;
use App\Models\User;

class StudyProgramPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::Admin, Role::Staff]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudyProgram $studyProgram): bool
    {
        if ($user->role === Role::Admin) {
            return true;
        } elseif ($user->role !== Role::Staff) {
            return false;
        } elseif ($user->info->faculty_id === null) {
            return true;
        }

        return $user->info->faculty_id === $studyProgram->faculty_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StudyProgram $studyProgram): bool
    {
        if ($user->role === Role::Admin) {
            return true;
        } elseif ($user->role !== Role::Staff) {
            return false;
        } elseif ($user->info->faculty_id === null) {
            return true;
        }

        return $user->info->faculty_id === $studyProgram->faculty_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StudyProgram $studyProgram): bool
    {
        return $user->role === Role::Admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StudyProgram $studyProgram): bool
    {
        return $user->role === Role::Admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StudyProgram $studyProgram): bool
    {
        return $user->role === Role::Admin;
    }
}
