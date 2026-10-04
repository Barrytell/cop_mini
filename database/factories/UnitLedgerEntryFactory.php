<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LedgerType;
use App\Models\User;
use App\Modules\Units\Models\UnitLedgerEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitLedgerEntry>
 */
class UnitLedgerEntryFactory extends Factory
{
    protected $model = UnitLedgerEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'units' => 100,
            'type' => LedgerType::AdminAdjustment,
            'reference_type' => null,
            'reference_id' => null,
            'note' => fake()->sentence(),
            'created_by' => null,
        ];
    }
}
