<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, News $news): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);
    }

    public function update(User $user, News $news): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_EMPLOYEE]);
    }

    public function delete(User $user, News $news): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }
}
