<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController
{
    public function __invoke(Request $request)
    {
        abort_unless($slug = $request->query('s'), 404);
        abort_unless($user = auth()->user(), 404);

        $attachment = Attachment::query()
            ->where('slug', $slug)
            ->firstOrFail();

        if ($user->role !== Role::Admin && $attachment->owner_id !== $user->id) {
            $class = basename($attachment->attachmentable_type);

            /** @var \App\Access\Attachment\Contracts\AttachmentAccess $access */
            if (class_exists($access = "App\\Access\\Attachment\\$class")) {
                abort_unless($access::check($attachment, $user), 404);
            }
        }

        return Storage::disk('local')->response($attachment->path, $attachment->name);
    }
}
