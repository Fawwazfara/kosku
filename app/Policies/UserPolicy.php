<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
