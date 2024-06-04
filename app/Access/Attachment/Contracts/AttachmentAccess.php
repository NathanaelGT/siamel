<?php

namespace App\Access\Attachment\Contracts;

use App\Models\Attachment;
use App\Models\User;

interface AttachmentAccess
{
    public static function check(Attachment $attachment, User $user): bool;
}
