<?php

namespace App\Policies;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KosPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Kos $kos): bool
    {
        if ($kos->status === Kos::STATUS_ACTIVE) {
            return true;
        }

        return $this->manage($user, $kos);
    }

    public function create(User $user): bool
    {
        return $user->isOwner() && $user->is_active;
    }

    public function accessOwnerArea(User $user): bool
    {
        return $user->isOwner() && $user->is_active;
    }

    public function update(User $user, Kos $kos): bool
    {
        return $this->manage($user, $kos);
    }

    public function updateStock(User $user, Kos $kos): bool
    {
        return $this->manage($user, $kos);
    }

    public function delete(User $user, Kos $kos): bool
    {
        return $this->manage($user, $kos);
    }

    public function managePhotos(User $user, Kos $kos): bool
    {
        return $this->manage($user, $kos);
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }

    protected function manage(?User $user, Kos $kos): bool
    {
        if (! $user) {
            return false;
        }

        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin() || ($user->isOwner() && $kos->owner_id === $user->id);
    }
}
