<?php

namespace App\Service\Auth;

use App\Models\User;

abstract class Invitation
{
    public static function encodeId(User $user): string
    {
        return base_convert($user->id, 10, 36);
    }

    public static function decodeId(string $decoded): int
    {
        return (int) base_convert($decoded, 36, 10);
    }

    public static function hashUser(User $user): string
    {
        return sha1(json_encode($user->only(['email', 'created_at'])));
    }
}
