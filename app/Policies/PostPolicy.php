<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Post $post): bool
    {
        return $post->author_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Post $post): bool
    {
        return $post->author_id === $user->id && in_array($post->status, [PostStatus::Draft, PostStatus::Returned], true);
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }

    public function submit(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}
