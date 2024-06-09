<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Faculty;
use App\Models\Staff;
use App\Models\User;

class FacultyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function view(User $user, Faculty $faculty): bool
    {
        return $user->role === Role::Admin && $this->adminCanManageFaculty($user->admin, $faculty);
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin && $user->admin->faculty_id === null;
    }

    public function update(User $user, Faculty $faculty): bool
    {
        return $user->role === Role::Admin && $this->adminCanManageFaculty($user->admin, $faculty);
    }

    public function delete(User $user, Faculty $faculty): bool
    {
        return $user->role === Role::Admin && $this->adminCanManageFaculty($user->admin, $faculty);
    }

    public function restore(User $user, Faculty $faculty): bool
    {
        return $user->role === Role::Admin && $this->adminCanManageFaculty($user->admin, $faculty);
    }

    public function forceDelete(User $user, Faculty $faculty): bool
    {
        return $user->role === Role::Admin && $this->adminCanManageFaculty($user->admin, $faculty);
    }

    protected function adminCanManageFaculty(Staff $admin, Faculty $faculty): bool
    {
        return in_array($admin->faculty_id, [null, $faculty->id]);
    }
}
