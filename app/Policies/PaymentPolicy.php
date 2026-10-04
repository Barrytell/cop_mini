<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Payments\Models\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->status === UserStatus::Active;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->user_id || ($user->isAdmin() && $user->status === UserStatus::Active);
    }

    public function create(User $user): bool
    {
        return $user->role->value === 'member'
            && in_array($user->status, [UserStatus::Pending, UserStatus::Active], true);
    }
}
