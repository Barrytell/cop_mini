<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\AdminPermission;

trait AuthorizesAdminPermission
{
    protected function requirePermission(string $permission): void
    {
        abort_unless(
            auth()->user() !== null && AdminPermission::allows(auth()->user(), $permission),
            403,
        );
    }
}
