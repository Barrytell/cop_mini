<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Modules\Settings\Models\Setting;
use App\Services\AuditLogService;
use App\Services\SettingsService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $this->authorize('manage', Setting::class);

        $social = setting('social_links', config('minimini.defaults.social_links'));
        if (! is_array($social)) {
            $social = [];
        }

        return view('admin.settings', [
            'unitPrice' => (string) setting('unit_price_usd', '0.01'),
            'referralBonus' => (int) setting('referral_bonus_units', 2),
            'minimum' => (string) setting('min_payment_usd', '10.00'),
            'siteName' => (string) setting('site_name', 'minimini.org'),
            'contactEmail' => (string) setting('contact_email', 'hello@minimini.org'),
            'social' => $social,
        ]);
    }

    public function update(UpdateSettingsRequest $request, SettingsService $settings, AuditLogService $audit): RedirectResponse
    {
        $social = [
            'facebook' => $request->input('social_facebook') ?? '',
            'x' => $request->input('social_x') ?? '',
            'instagram' => $request->input('social_instagram') ?? '',
            'linkedin' => $request->input('social_linkedin') ?? '',
            'youtube' => $request->input('social_youtube') ?? '',
        ];

        $previous = [
            'unit_price_usd' => (string) $settings->get('unit_price_usd', '0.01'),
            'referral_bonus_units' => (int) $settings->get('referral_bonus_units', 2),
            'min_payment_usd' => (string) $settings->get('min_payment_usd', '10.00'),
            'site_name' => (string) $settings->get('site_name', 'minimini.org'),
            'contact_email' => (string) $settings->get('contact_email', 'hello@minimini.org'),
            'social_links' => $settings->get('social_links', config('minimini.defaults.social_links')),
        ];

        $next = [
            'unit_price_usd' => Money::normalize($request->string('unit_price_usd')->toString(), 6),
            'referral_bonus_units' => $request->integer('referral_bonus_units'),
            'min_payment_usd' => Money::normalize($request->string('min_payment_usd')->toString(), 2),
            'site_name' => $request->string('site_name')->toString(),
            'contact_email' => $request->string('contact_email')->toString(),
            'social_links' => $social,
        ];

        DB::transaction(function () use ($settings, $audit, $request, $previous, $next): void {
            foreach ($next as $key => $value) {
                $settings->put($key, $value);
            }

            $audit->record(
                actor: $request->user(),
                action: 'settings.updated',
                subject: Setting::query()->where('key', Setting::SITE_NAME)->first(),
                oldValues: $previous,
                newValues: $next,
                request: $request,
            );
        });

        return redirect()->route('admin.settings.edit')->with('status', 'Settings saved.');
    }
}
