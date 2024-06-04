<?php

namespace App\Access\Attachment;

use App\Models\Attachment;
use App\Models\User;

abstract class Submission implements Contracts\AttachmentAccess
{
    public static function check(Attachment $attachment, User $user): bool
    {
        return $user->can('view', $attachment->attachmentable);
    }
}
