<?php

namespace App\Observers;

use App\Models\Post;

class PostObserver
{
    public function creating(Post $post): void
    {
        $now = now()->toDateTimeString();

        $post->user_id = auth()->id();
        $post->published_at ??= $now;
        $post->created_at = $now;
        $post->updated_at = $now;
    }
}
