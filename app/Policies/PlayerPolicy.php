<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;

class PlayerPolicy
{
    public function viewAny(?User $user): bool
    {
        if ($user === null) {
            return true;
        }

        return $user->hasAnyRole([
            User::ROLE_ADMIN,
            User::ROLE_EMPLOYEE,
            User::ROLE_FAN,
        ]);
    }

    public function view(?User $user, Player $player): bool
    {
        if ($user === null) {
            return true;
        }

        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);
    }

    public function update(User $user, Player $player): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);
    }

    public function delete(User $user, Player $player): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }
}
