<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Post;
use App\Models\StudentSubject;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return match ($user->role) {
            Role::Professor => $user->id === $post->user_id,
            Role::Student   => $post->relationLoaded('subject') && $post->subject->relationLoaded('studentSubjects')
                ? $post->subject->studentSubjects->contains('student_id', $user->info_id)
                : StudentSubject::query()
                    ->where('student_id', $user->info_id)
                    ->where('subject_id', $post->subject_id)
                    ->exists(),
            default         => false,
        };
    }

    public function create(User $user): bool
    {
        return match ($user->role) {
            Role::Professor => true,
            default         => false,
        };
    }

    public function update(User $user, Post $post): bool
    {
        return match ($user->role) {
            Role::Professor => $user->id === $post->user_id,
            default         => false,
        };
    }

    public function delete(User $user, Post $post): bool
    {
        return match ($user->role) {
            Role::Professor => $user->id === $post->user_id,
            default         => false,
        };
    }

    public function restore(User $user, Post $post): bool
    {
        return match ($user->role) {
            Role::Professor => $user->id === $post->user_id,
            default         => false,
        };
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return match ($user->role) {
            Role::Professor => $user->id === $post->user_id,
            default         => false,
        };
    }
}
