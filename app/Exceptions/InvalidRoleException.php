<?php

namespace App\Exceptions;

use App\Enums\Role;
use RuntimeException;

class InvalidRoleException extends RuntimeException
{
    public function __construct(Role | array $expect, Role $actual)
    {
        $expectedRoles = collect($expect)
            ->map(fn(Role $role) => "[$role->name]")
            ->join(' or ');

        parent::__construct("Unexpected role: [$actual->name], Expected $expectedRoles");
    }
}
