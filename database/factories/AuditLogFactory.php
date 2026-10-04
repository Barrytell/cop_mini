<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'action' => 'settings.updated',
            'auditable_type' => null,
            'auditable_id' => null,
            'old_values' => ['site_name' => 'Old'],
            'new_values' => ['site_name' => 'New'],
            'ip_address' => '203.0.113.10',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ];
    }
}
