<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return match ($user->role) {
            Role::Professor => $attendance->subjectSchedule()
                    ->first('subject_id')
                    ?->subject()
                    ->value('professor_id') === $user->info_id,
            Role::Student   => $attendance->student_id === $user->info_id,
            default         => false,
        };
    }

    public function create(User $user): bool
    {
        return match ($user->role) {
            Role::Professor => true,
            default         => false,
        };
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return match ($user->role) {
            Role::Professor => $attendance->subjectSchedule()
                    ->first('subject_id')
                    ?->subject()
                    ->value('professor_id') !== $user->info_id,
            default         => false,
        };
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return match ($user->role) {
            Role::Professor => $attendance->subjectSchedule()
                    ->first('subject_id')
                    ?->subject()
                    ->value('professor_id') === $user->info_id,
            default         => false,
        };
    }

    public function restore(User $user, Attendance $attendance): bool
    {
        return match ($user->role) {
            Role::Professor => $attendance->subjectSchedule()
                    ->first('subject_id')
                    ?->subject()
                    ->value('professor_id') === $user->info_id,
            default         => false,
        };
    }

    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return match ($user->role) {
            Role::Professor => $attendance->subjectSchedule()
                    ->first('subject_id')
                    ?->subject()
                    ->value('professor_id') === $user->info_id,
            default         => false,
        };
    }
}
