<?php

declare(strict_types=1);

namespace App\Modules\Settings\Models;

use App\Enums\SettingType;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    public const UNIT_PRICE_USD = 'unit_price_usd';

    public const REFERRAL_BONUS_UNITS = 'referral_bonus_units';

    public const MIN_PAYMENT_USD = 'min_payment_usd';

    public const SITE_NAME = 'site_name';

    public const CONTACT_EMAIL = 'contact_email';

    public const SOCIAL_LINKS = 'social_links';

    public const WHATSAPP_NUMBER = 'whatsapp_number';

    public const OFFICE_ADDRESS = 'office_address';

    public const MAP_EMBED_URL = 'map_embed_url';

    public const REGISTRATION_OPEN = 'registration_open';

    public const REFERRAL_PROGRAM_ENABLED = 'referral_program_enabled';

    public const MAINTENANCE_MODE = 'maintenance_mode';

    /**
     * @var array<string, array{type: SettingType, group: string}>
     */
    public const DEFINITIONS = [
        self::UNIT_PRICE_USD => ['type' => SettingType::Decimal, 'group' => 'payments'],
        self::REFERRAL_BONUS_UNITS => ['type' => SettingType::Integer, 'group' => 'referrals'],
        self::MIN_PAYMENT_USD => ['type' => SettingType::Decimal, 'group' => 'payments'],
        self::SITE_NAME => ['type' => SettingType::String, 'group' => 'general'],
        self::CONTACT_EMAIL => ['type' => SettingType::String, 'group' => 'general'],
        self::SOCIAL_LINKS => ['type' => SettingType::Json, 'group' => 'social'],
        self::WHATSAPP_NUMBER => ['type' => SettingType::String, 'group' => 'general'],
        self::OFFICE_ADDRESS => ['type' => SettingType::String, 'group' => 'general'],
        self::MAP_EMBED_URL => ['type' => SettingType::String, 'group' => 'general'],
        self::REGISTRATION_OPEN => ['type' => SettingType::Boolean, 'group' => 'general'],
        self::REFERRAL_PROGRAM_ENABLED => ['type' => SettingType::Boolean, 'group' => 'referrals'],
        self::MAINTENANCE_MODE => ['type' => SettingType::Boolean, 'group' => 'general'],
    ];

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
        ];
    }

    public function castValue(): mixed
    {
        return match ($this->type) {
            SettingType::Integer => (int) $this->value,
            SettingType::Decimal => (string) $this->value,
            SettingType::Boolean => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            SettingType::Json => json_decode((string) $this->value, true) ?? [],
            SettingType::String => (string) $this->value,
        };
    }

    protected static function newFactory(): SettingFactory
    {
        return SettingFactory::new();
    }
}
