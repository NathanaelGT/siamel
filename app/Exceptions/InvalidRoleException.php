<?php

namespace App\Exceptions;

use App\Enums\Role;
use RuntimeException;

class InvalidRoleException extends RuntimeException
{
    public function __construct(Role | array $expect, ?Role $actual)
    {
        $expectedRoles = collect($expect)
            ->map(fn(Role $role) => "[$role->name]")
            ->join(' or ');

        $unexpected = $actual?->name ?? 'null';

        parent::__construct("Unexpected role: [$unexpected], Expected $expectedRoles");
    }
}
