<?php

declare(strict_types=1);

namespace App\Modules\Members\Actions;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Modules\Referrals\Actions\AttachReferral;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterMember
{
    public function __construct(private readonly AttachReferral $attachReferral) {}

    /**
     * @param  array{name: string, email: string, phone: string, country: string, password: string, referral_code?: string|null}  $data
     */
    public function handle(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country' => $data['country'],
                'password' => $data['password'],
                'role' => UserRole::Member,
                'status' => UserStatus::Pending,
            ]);

            $this->attachReferral->handle($user, $data['referral_code'] ?? null);

            return $user->refresh();
        });

        event(new Registered($user));
        Auth::login($user);

        return $user;
    }
}
