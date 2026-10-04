<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SettingType;
use App\Modules\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SettingsService
{
    private const CACHE_KEY = 'minimini.settings';

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        if (func_num_args() > 1) {
            return $default;
        }

        return config('minimini.defaults.'.$key);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function (): array {
            return Setting::query()
                ->get()
                ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->castValue()])
                ->all();
        });
    }

    public function put(string $key, mixed $value): Setting
    {
        if (! array_key_exists($key, Setting::DEFINITIONS)) {
            throw new InvalidArgumentException("Unknown setting [{$key}].");
        }

        $definition = Setting::DEFINITIONS[$key];

        $setting = DB::transaction(function () use ($key, $value, $definition): Setting {
            $setting = Setting::query()->where('key', $key)->lockForUpdate()->first();

            if ($setting === null) {
                $setting = new Setting([
                    'key' => $key,
                    'type' => $definition['type'],
                    'group' => $definition['group'],
                ]);
            }

            $setting->value = $this->serialize($definition['type'], $value);
            $setting->type = $definition['type'];
            $setting->group = $definition['group'];
            $setting->save();

            return $setting;
        });

        DB::afterCommit(fn () => $this->forget());

        return $setting->refresh();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function serialize(SettingType $type, mixed $value): string
    {
        return match ($type) {
            SettingType::Integer => (string) (int) $value,
            SettingType::Decimal => (string) $value,
            SettingType::Boolean => $value ? '1' : '0',
            SettingType::Json => json_encode($value, JSON_THROW_ON_ERROR),
            SettingType::String => (string) $value,
        };
    }
}
