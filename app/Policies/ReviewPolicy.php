<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, ): bool
    {
        return $user->isStudent() && $user->is_active;
    }

    public function update(User $user, Review $review): bool
    {
        return $user->is_active && ($user->isAdmin() || $review->user_id === $user->id);
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->is_active && ($user->isAdmin() || $review->user_id === $user->id);
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
