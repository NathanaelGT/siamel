<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;

class AccessibleEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $staffAccountQuery = User::query()
            ->join('staff', 'users.id', '=', 'staff.user_id');

        (clone $staffAccountQuery)
            ->whereNull('faculty_id')
            ->where('role', Role::Admin)
            ->first(['users.*'])
            ->updateQuietly([
                'email' => 'admin@siamel.test',
            ]);

        (clone $staffAccountQuery)
            ->where('faculty_id', 8)
            ->where('role', Role::Admin)
            ->first(['users.*'])
            ->updateQuietly([
                'email' => 'admin.fasilkom@siamel.test',
            ]);

        (clone $staffAccountQuery)
            ->whereNull('faculty_id')
            ->where('role', Role::Staff)
            ->first(['users.*'])
            ->updateQuietly([
                'email' => 'staff@siamel.test',
            ]);

        (clone $staffAccountQuery)
            ->where('faculty_id', 8)
            ->where('role', Role::Staff)
            ->first(['users.*'])
            ->updateQuietly([
                'email' => 'staff.fasilkom@siamel.test',
            ]);

        User::query()
            ->join('professors', 'users.id', '=', 'professors.user_id')
            ->where('faculty_id', 8)
            ->where('role', Role::Professor)
            ->first(['users.*'])
            ->updateQuietly([
                'email' => 'dosen@siamel.test',
            ]);
    }
}
