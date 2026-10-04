<?php

declare(strict_types=1);

namespace App\Modules\Members\Actions;

use App\Models\User;

class AssignMemberNumber
{
    public function handle(User $user): User
    {
        if (! str_starts_with((string) $user->member_no, 'TMP-')) {
            return $user;
        }

        $user->forceFill([
            'member_no' => sprintf('MM-%06d', $user->id),
        ])->save();

        return $user;
    }
}
