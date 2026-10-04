<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+1555'.fake()->unique()->numerify('#######'),
            'country' => 'United States',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Member,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatus::Pending,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatus::Suspended,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (): array => [
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (): array => [
            'role' => UserRole::SuperAdmin,
            'status' => UserStatus::Active,
        ]);
    }
}
