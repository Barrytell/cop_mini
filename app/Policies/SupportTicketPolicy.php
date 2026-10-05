<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Modules\Support\Models\SupportTicket;
use App\Support\AdminPermission;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return AdminPermission::allows($user, AdminPermission::SUPPORT);
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        return $user->id === $ticket->user_id
            || AdminPermission::allows($user, AdminPermission::SUPPORT);
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function close(User $user, SupportTicket $ticket): bool
    {
        return AdminPermission::allows($user, AdminPermission::SUPPORT);
    }
}
