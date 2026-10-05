<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Cms\Models\Banner;
use App\Modules\Cms\Models\Page;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Gateways\FlutterwaveGateway;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Settings\Models\Setting;
use App\Modules\Units\Models\UnitLedgerEntry;
use App\Policies\AnnouncementPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BannerPolicy;
use App\Policies\MeetingPolicy;
use App\Policies\PagePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReferralPolicy;
use App\Policies\SettingPolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\UnitLedgerPolicy;
use App\Policies\UserPolicy;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');

        $this->app->bind(PaymentGatewayInterface::class, FlutterwaveGateway::class);
    }

    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(UnitLedgerEntry::class, UnitLedgerPolicy::class);
        Gate::policy(Referral::class, ReferralPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Meeting::class, MeetingPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Banner::class, BannerPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(SupportTicket::class, SupportTicketPolicy::class);

        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('payments', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(10)->by((string) $key);
        });

        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(120)->by((string) $request->ip());
        });

        RateLimiter::for('support', function (Request $request) {
            return Limit::perMinute(5)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by((string) $request->ip());
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $proxies = config('minimini.trusted_proxies');

        if (is_string($proxies) && $proxies !== '') {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        View::composer([
            'layouts.*',
            'home',
            'auth.*',
            'pages.*',
            'news.*',
            'events.*',
            'faq.*',
            'testimonials.*',
            'gallery.*',
            'downloads.*',
            'contact.*',
            'errors.*',
            'member.*',
            'admin.*',
            'account.*',
        ], function ($view): void {
            $defaults = config('minimini.defaults');

            try {
                $social = setting('social_links', $defaults['social_links']);
                $view->with('accountUrl', auth()->check()
                    ? app(\App\Support\RedirectsAuthenticatedUser::class)->url(auth()->user())
                    : null);
                $view->with('siteName', (string) setting('site_name', $defaults['site_name']));
                $view->with('contactEmail', (string) setting('contact_email', $defaults['contact_email']));
                $view->with('socialLinks', is_array($social) ? $social : []);
                $view->with('navPages', Page::query()->inNav()->get(['title', 'slug', 'sort_order']));
                $view->with('footerPages', Page::query()->published()->orderBy('sort_order')->orderBy('title')->get(['title', 'slug']));
                $view->with('whatsappNumber', (string) setting('whatsapp_number', $defaults['whatsapp_number'] ?? ''));
                $view->with('officeAddress', (string) setting('office_address', $defaults['office_address'] ?? ''));
                $view->with('referralBonus', (int) setting('referral_bonus_units', $defaults['referral_bonus_units']));
            } catch (\Throwable) {
                $view->with('accountUrl', null);
                $view->with('siteName', $defaults['site_name']);
                $view->with('contactEmail', $defaults['contact_email']);
                $view->with('socialLinks', $defaults['social_links']);
                $view->with('navPages', collect());
                $view->with('footerPages', collect());
                $view->with('whatsappNumber', (string) ($defaults['whatsapp_number'] ?? ''));
                $view->with('officeAddress', (string) ($defaults['office_address'] ?? ''));
                $view->with('referralBonus', (int) $defaults['referral_bonus_units']);
            }
        });

        View::composer('layouts.member', function ($view): void {
            $user = auth()->user();

            if ($user === null) {
                $view->with('unreadNotificationCount', 0);
                $view->with('unreadAnnouncementCount', 0);

                return;
            }

            $view->with('unreadNotificationCount', $user->unreadNotifications()->count());
            $view->with('unreadAnnouncementCount', Announcement::query()
                ->published()
                ->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
                ->count());
        });
    }
}
