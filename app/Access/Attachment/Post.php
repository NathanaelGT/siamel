<?php

namespace App\Access\Attachment;

use App\Models\Attachment;
use App\Models\Subject;
use App\Models\User;

abstract class Post implements Contracts\AttachmentAccess
{
    public static function check(Attachment $attachment, User $user): bool
    {
        return $user->can('view', Subject::query()
            ->where('id', $attachment->attachmentable()->select('subject_id')->toBase())
            ->firstOrFail());
    }
}
