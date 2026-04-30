<?php

namespace App\Policies;

use App\Models\Match;
use App\Models\User;

class MatchPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Match $match): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);
    }

    public function update(User $user, Match $match): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);
    }

    public function delete(User $user, Match $match): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }
}
