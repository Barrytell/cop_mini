# minimini.org

Membership platform for a cooperative that pools member funds into real estate, gold, oil, and other stable assets.

Members register as **pending**, pay through Flutterwave, and become **active** only after the server verifies that payment. Units are credited from the unit price stored on that payment. A referral bonus is credited once, when the referred person first becomes active.

## Stack

- PHP 8.3, Laravel 11
- MySQL 8
- Blade, Tailwind CSS, Alpine.js, Vite
- Database queue
- SMTP mail from `.env`

## Local setup

1. Install PHP 8.3 (extensions: `bcmath`, `curl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `fileinfo`), Composer, Node 20+, and MySQL 8.
2. Create the database and user:

```sql
CREATE DATABASE minimini CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'minimini'@'127.0.0.1' IDENTIFIED BY 'a-strong-password';
GRANT ALL PRIVILEGES ON minimini.* TO 'minimini'@'127.0.0.1';
```

3. Install and configure:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env`: database credentials, `SUPER_ADMIN_PASSWORD`, SMTP, and Flutterwave keys. For local HTTP (`php artisan serve`), set `SESSION_SECURE_COOKIE=false`. Production must keep it `true` and serve HTTPS only.

4. Build assets, migrate, and seed:

```bash
npm install
npm run build
php artisan migrate:fresh --seed
```

`SEED_DEMO_DATA=true` adds sample members, payments, an announcement, a meeting, and two pages. Leave it `false` in production.

5. Run the app and the queue worker (verification mail and payment receipts are queued):

```bash
php artisan serve
php artisan queue:work database --tries=3
```

Demo passwords when demo data is seeded:

| Account | Email | Password |
| --- | --- | --- |
| Super admin | value of `SUPER_ADMIN_EMAIL` | value of `SUPER_ADMIN_PASSWORD` |
| Active member | ada@minimini.test | password |
| Active referred member | ben@minimini.test | password |
| Pending member | chioma@minimini.test | password |

Referral code for Ada is `ADA4GOLD`.

## Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database. Money math uses decimal strings and BCMath, not floats.

## How money works

- Base currency is USD.
- `amount_usd`, `amount_paid`, and `unit_price_snapshot` are `DECIMAL` columns.
- Unit balances are the sum of append-only `units_ledger` rows (signed integers).
- Units credited = `amount_usd / unit_price_snapshot`, truncated toward zero.
- Successful payments and ledger rows cannot be updated or deleted.
- A collected charge must be at least the quoted amount, in the currency sent to Flutterwave. Units are still the snapshotted USD amount divided by the snapshotted unit price.
- Referral bonus uses the `referral_bonus_units` setting at the moment the referred member becomes active, and it is written once.

## Flutterwave

Hosted checkout uses Flutterwave API v3. In the Flutterwave dashboard:

1. Copy the public key, secret key, and encryption key into `FLW_PUBLIC_KEY`, `FLW_SECRET_KEY`, and `FLW_ENCRYPTION_KEY`.
2. Set a secret hash and put the same value in `FLW_WEBHOOK_HASH`.
3. Set the webhook URL to `https://minimini.org/webhooks/flutterwave`. Flutterwave sends `charge.completed` and failed events with the `verif-hash` header. The endpoint is exempt from CSRF and checks that header before it does anything else.
4. Set `FLW_REDIRECT_URL` to `https://minimini.org/member/payments/callback`. The callback reads the transaction id only so it can ask Flutterwave to verify. It does not trust the status or amount in the query string.

Every confirmation goes through `ConfirmPayment`, which locks the payment row. The same transaction reference can be processed twice and will credit units only once. Pending payments older than 10 minutes are re-checked hourly by `php artisan payments:reconcile` (scheduled). A checkout with no Flutterwave transaction after 24 hours is marked cancelled. The member can start a new payment, and a late successful webhook can still confirm a cancelled row.

Units are calculated from the USD amount. If the member pays in another supported currency, Flutterwave's rate endpoint supplies the charge amount. Card, bank transfer, USSD, and mobile money are requested through `payment_options` for the currencies that support them.

## Member area

Signed-in members use `/member`. The dashboard and unit purchase page stay limited to active members. Pending members can open payments, referrals, the units statement, announcements, meetings, support, notifications, and profile.

- A `?ref=` code on any public page is stored in the session and in a `referral_code` cookie for 30 days, then attached when that visitor registers. A member's own code is ignored. The bonus is not paid at signup.
- `referral_bonus_units` (default 2) is credited once, after the referred member's first payment is confirmed. The row is `referral_bonus` on `units_ledger` and `rewarded` on `referrals`. A second payment, a repeated confirmation, or a second attach does not pay it again.
- The units statement at `/member/units` filters by type and date and exports CSV.
- Profile payout and next-of-kin fields are encrypted at rest. Notification preferences are `notify_payments`, `notify_announcements`, and `notify_meetings`.
- Announcements, meetings (with RSVP), the notification bell, and support tickets live under `/member`. Admins reply from `/admin/support`.
- Banners are the `x-banner-slider` component, used on the home page and the member dashboard.

## Public site

The marketing site is the navy and gold pages under `/`. Copy lives in the database (`pages`, `posts`, `faqs`, `testimonials`, `team_members`, `gallery_items`, `downloads`) so it can be edited later. Page bodies are plain text: a blank line starts a block, `## ` is a heading, and `- ` is a list. They are escaped on the way out.

- Home, about, mission, leadership, how it works, investment areas, membership, referrals, news, events, FAQ, testimonials, gallery, downloads, contact, terms, privacy, and risk disclosure.
- `/contact` stores the message, then queues mail to `contact_email` when that address is valid.
- `/sitemap.xml` and `/robots.txt` are generated by the app. Do not put a static `public/robots.txt` back in front of the route.
- A cookie notice stores `minimini.cookie` in local storage after Accept. It is a preference, not a tracker.
- The WhatsApp button uses `whatsapp_number` from settings and stays hidden until that number has at least 8 digits.
- The office map iframe accepts only HTTPS OpenStreetMap or Google Maps URLs from `map_embed_url`.

`php artisan db:seed --class=PublicContentSeeder` writes the public copy, icons, gallery images, and the two PDFs in `public/files`. With `SEED_DEMO_DATA=true` it also sets a demo WhatsApp number.

## Admin

`/admin` is limited to `admin` and `super_admin`, with a collapsible sidebar (desktop) and drawer (mobile). Granular permissions cover dashboard, members, payments, settings, referrals, communications, CMS, banners, support, admins, reports, and system. `super_admin` always has full access. Regular admins with an empty permission list get dashboard and support only.

- **Dashboard** — KPI cards, signup/revenue charts with date filters, recent payments, and pending counts.
- **Members** — searchable/filterable/paginated list, detail (profile, payments, ledger, referrals), activate/suspend, edit, password reset, units adjust (`admin_adjustment` ledger note required), soft delete/restore, CSV export. Impersonation is `super_admin` only and is audited; return via the gold banner.
- **Payments** — filters, gateway payload, Flutterwave re-verify, manual mark-successful (`super_admin`, audited), refunds log, CSV export.
- **Unit & settings** — unit price (future payments only; history in `setting_changes`), referral bonus, minimum payment, registration / referral programme / maintenance toggles. Site settings (name, contact, social, map) stay under Site settings.
- **Referrals** — list, leaderboard, manual reward/reverse (audited).
- **Communications** — announcements (audience, pin, schedule, email/in-app queue, attachments), meetings + RSVP export, bulk email/SMS-ready composer, email templates.
- **CMS** — pages, posts/categories, FAQ, testimonials, team, gallery, downloads, menus.
- **Banners** — upload, reorder, enable/disable, home or member dashboard placement, preview.
- **Contact & support** — contact inbox and member tickets with reply/close.
- **Admins** — create/edit admins, assign permissions, TOTP 2FA setup/challenge.
- **System** — audit log, queue/failed jobs, webhook log, last reconcile, backup trigger.
- **Reports** — financial/membership/units/referral summary with date filters, CSV and PDF export.

Every admin write goes through policies or permission checks, FormRequest/controller validation, and the audit log. Optional admin TOTP: after enable, login redirects to `/admin/totp/challenge` until verified for that session.

## Deploy

1. Point the web root at `public/`. Redirect all HTTP to HTTPS.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://minimini.org`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`.
3. Set `TRUSTED_PROXIES` to the load balancer address (or `*` only when every hop in front of PHP is yours).
4. `composer install --no-dev --optimize-autoloader`
5. `npm ci && npm run build`
6. `php artisan migrate --force`
7. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
8. Run `php artisan queue:work database --sleep=1 --tries=3` under a process supervisor.
9. Schedule `php artisan schedule:run` every minute. That runs `payments:reconcile` hourly.
10. Keep secrets in the server environment only. Do not commit `.env`.
11. Run a queue worker so payment receipts and referral mail leave the database queue.

Flutterwave dashboard setup is in the Flutterwave section above. The app confirms every payment with Flutterwave's verify endpoint before crediting units.

## Module map

`app/Modules` holds Members, Payments, Units, Referrals, Announcements, Meetings, Cms, and Settings. Cross-cutting services live in `app/Services`. New payment providers implement `App\Modules\Payments\Contracts\PaymentGatewayInterface` and are bound in `AppServiceProvider`.
