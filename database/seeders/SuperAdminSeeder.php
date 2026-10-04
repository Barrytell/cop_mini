<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('minimini.super_admin');
        $email = $admin['email'] ?? null;
        $password = $admin['password'] ?? null;
        $phone = $admin['phone'] ?? null;

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '' || ! is_string($phone) || $phone === '') {
            throw new RuntimeException('Set SUPER_ADMIN_EMAIL, SUPER_ADMIN_PASSWORD, and SUPER_ADMIN_PHONE before seeding.');
        }

        $country = (string) ($admin['country'] ?: 'United States');

        if (! in_array($country, config('countries'), true)) {
            throw new RuntimeException('SUPER_ADMIN_COUNTRY must match a country in config/countries.php.');
        }

        User::query()->updateOrCreate(
            ['email' => strtolower($email)],
            [
                'name' => (string) ($admin['name'] ?: 'Super Admin'),
                'phone' => $phone,
                'country' => $country,
                'password' => $password,
                'role' => UserRole::SuperAdmin,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );
    }
}
