<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdminPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUnitSettingsRequest;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Models\SettingChange;
use App\Services\AuditLogService;
use App\Services\SettingsService;
use App\Support\AdminPermission;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitSettingController extends Controller
{
    use AuthorizesAdminPermission;

    public function edit(): View
    {
        $this->requirePermission(AdminPermission::SETTINGS);

        return view('admin.settings.units', [
            'unitPrice' => Money::present((string) setting('unit_price_usd', '0.01')),
            'minPayment' => Money::present((string) setting('min_payment_usd', '10.00')),
            'referralBonus' => (int) setting('referral_bonus_units', 2),
            'registrationOpen' => (bool) setting('registration_open', true),
            'referralProgramEnabled' => (bool) setting('referral_program_enabled', true),
            'maintenanceMode' => (bool) setting('maintenance_mode', false),
            'priceHistory' => SettingChange::query()->where('key', Setting::UNIT_PRICE_USD)->latest('id')->limit(20)->with('actor')->get(),
        ]);
    }

    public function update(UpdateUnitSettingsRequest $request, SettingsService $settings, AuditLogService $audit): RedirectResponse
    {
        $this->requirePermission(AdminPermission::SETTINGS);

        $keys = [
            'unit_price_usd' => Money::normalize($request->string('unit_price_usd')->toString(), 6),
            'min_payment_usd' => Money::normalize($request->string('min_payment_usd')->toString(), 2),
            'referral_bonus_units' => (int) $request->integer('referral_bonus_units'),
            'registration_open' => $request->boolean('registration_open'),
            'referral_program_enabled' => $request->boolean('referral_program_enabled'),
            'maintenance_mode' => $request->boolean('maintenance_mode'),
        ];

        $previous = [];
        $next = [];

        foreach ($keys as $key => $value) {
            $old = setting($key);
            $previous[$key] = $old;
            $settings->put($key, $value);
            $next[$key] = $value;

            if ((string) $old !== (string) (is_bool($value) ? ($value ? '1' : '0') : $value)) {
                SettingChange::query()->create([
                    'key' => $key,
                    'old_value' => is_bool($old) ? ($old ? '1' : '0') : (string) $old,
                    'new_value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'changed_by' => $request->user()->id,
                    'note' => $key === 'unit_price_usd' ? 'Applies to future payments only.' : null,
                    'created_at' => now(),
                ]);
            }
        }

        $audit->record($request->user(), 'admin.settings.units_updated', null, $previous, $next, $request);

        return back()->with('status', 'Unit and programme settings saved.');
    }
}
