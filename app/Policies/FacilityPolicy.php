<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;

class FacilityPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Facility $facility): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Facility $facility): bool
    {
        return $user->isAdmin();
    }
}
