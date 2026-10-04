<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->status === UserStatus::Active;
    }

    public function view(User $user, User $subject): bool
    {
        return $user->is($subject) || ($user->isAdmin() && $user->status === UserStatus::Active);
    }

    public function update(User $user, User $subject): bool
    {
        return $user->isAdmin() && $user->status === UserStatus::Active && ! $user->is($subject);
    }

    public function suspend(User $user, User $subject): bool
    {
        return $this->update($user, $subject) && $subject->role === UserRole::Member;
    }

    public function delete(User $user, User $subject): bool
    {
        return $user->role === UserRole::SuperAdmin
            && $user->status === UserStatus::Active
            && ! $user->is($subject);
    }
}
