<?php

namespace App\Enums;

use App\Models\Professor;
use App\Models\Staff;
use App\Models\Student;

enum Role
{
    case Admin;
    case Staff;
    case Professor;
    case Student;

    /** @returns class-string */
    public function model(): string
    {
        return match ($this) {
            Role::Admin, Role::Staff => Staff::class,
            Role::Professor          => Professor::class,
            Role::Student            => Student::class,
        };
    }
}
