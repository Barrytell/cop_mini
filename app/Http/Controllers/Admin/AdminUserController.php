<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    use AuthorizesAdminPermission;

    public function index(): View
    {
        $this->requirePermission(AdminPermission::ADMINS);

        return view('admin.admins.index', [
            'admins' => User::query()->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])->orderBy('name')->paginate(20),
            'labels' => AdminPermission::labels(),
        ]);
    }

    public function create(): View
    {
        $this->requirePermission(AdminPermission::ADMINS);

        return view('admin.admins.form', [
            'admin' => new User(['role' => UserRole::Admin, 'status' => UserStatus::Active, 'permissions' => [AdminPermission::DASHBOARD]]),
            'labels' => AdminPermission::labels(),
        ]);
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::ADMINS);
        abort_unless($request->user()?->role === UserRole::SuperAdmin, 403);

        $data = $this->validated($request);
        $admin = User::query()->create([
            ...$data,
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'country' => $data['country'],
            'email_verified_at' => now(),
        ]);
        unset($data['password']);
        $audit->record($request->user(), 'admin.admin_user.created', $admin, null, $data, $request);

        return redirect()->route('admin.admins.index')->with('status', 'Admin created.');
    }

    public function edit(User $admin): View
    {
        $this->requirePermission(AdminPermission::ADMINS);
        abort_unless($admin->isAdmin(), 404);

        return view('admin.admins.form', [
            'admin' => $admin,
            'labels' => AdminPermission::labels(),
        ]);
    }

    public function update(Request $request, User $admin, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::ADMINS);
        abort_unless($admin->isAdmin(), 404);
        abort_unless($request->user()?->role === UserRole::SuperAdmin || $request->user()?->is($admin), 403);

        $data = $this->validated($request, $admin);
        $previous = $admin->only(['name', 'email', 'phone', 'country', 'role', 'status', 'permissions']);

        if (! empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        unset($data['password']);
        $admin->fill($data)->save();
        $audit->record($request->user(), 'admin.admin_user.updated', $admin, $previous, $data, $request);

        return redirect()->route('admin.admins.index')->with('status', 'Admin updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $admin = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin?->id)],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s().-]{6,18}$/'],
            'country' => ['required', 'string', 'max:80'],
            'role' => ['required', Rule::in([UserRole::Admin->value, UserRole::SuperAdmin->value])],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(AdminPermission::all())],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->user()?->role !== UserRole::SuperAdmin) {
            $data['role'] = $admin?->role->value ?? UserRole::Admin->value;
            $data['permissions'] = $admin?->permissions ?? [];
        }

        $data['permissions'] = array_values(array_unique($data['permissions'] ?? []));

        return $data;
    }
}
