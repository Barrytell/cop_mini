<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Settings\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = config('minimini.defaults');

        foreach (Setting::DEFINITIONS as $key => $definition) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $definition['type']->value === 'json'
                        ? json_encode($defaults[$key], JSON_THROW_ON_ERROR)
                        : (string) $defaults[$key],
                    'type' => $definition['type'],
                    'group' => $definition['group'],
                ],
            );
        }

        app(SettingsService::class)->forget();
    }
}
