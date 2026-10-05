<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\AdminPermission;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return AdminPermission::allows($user, AdminPermission::MEMBERS);
    }

    public function view(User $user, User $subject): bool
    {
        if ($user->is($subject)) {
            return true;
        }

        if ($subject->isAdmin()) {
            return AdminPermission::allows($user, AdminPermission::ADMINS);
        }

        return AdminPermission::allows($user, AdminPermission::MEMBERS);
    }

    public function update(User $user, User $subject): bool
    {
        if ($user->is($subject)) {
            return AdminPermission::allows($user, AdminPermission::ADMINS) || $user->role === UserRole::SuperAdmin;
        }

        if ($subject->isAdmin()) {
            return $user->role === UserRole::SuperAdmin && $user->status === UserStatus::Active;
        }

        return AdminPermission::allows($user, AdminPermission::MEMBERS);
    }

    public function suspend(User $user, User $subject): bool
    {
        return $this->update($user, $subject) && $subject->role === UserRole::Member;
    }

    public function activate(User $user, User $subject): bool
    {
        return $this->update($user, $subject) && $subject->role === UserRole::Member;
    }

    public function adjustUnits(User $user, User $subject): bool
    {
        return AdminPermission::allows($user, AdminPermission::MEMBERS)
            && $subject->role === UserRole::Member;
    }

    public function impersonate(User $user, User $subject): bool
    {
        return $user->role === UserRole::SuperAdmin
            && $user->status === UserStatus::Active
            && ! $user->is($subject)
            && $subject->role === UserRole::Member
            && $subject->status !== UserStatus::Suspended;
    }

    public function resetPassword(User $user, User $subject): bool
    {
        return $this->update($user, $subject);
    }

    public function delete(User $user, User $subject): bool
    {
        return $user->role === UserRole::SuperAdmin
            && $user->status === UserStatus::Active
            && ! $user->is($subject);
    }

    public function restore(User $user, User $subject): bool
    {
        return $this->delete($user, $subject);
    }

    public function manageAdmins(User $user): bool
    {
        return AdminPermission::allows($user, AdminPermission::ADMINS);
    }
}
