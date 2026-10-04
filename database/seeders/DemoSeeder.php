<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MeetingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Cms\Models\Banner;
use App\Modules\Cms\Models\Page;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Payments\Actions\VerifyPayment;
use App\Modules\Payments\Data\PaymentVerification;
use App\Modules\Payments\Models\Payment;
use App\Modules\Referrals\Actions\AttachReferral;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('minimini.seed_demo_data')) {
            return;
        }

        $admin = User::query()->where('email', strtolower((string) config('minimini.super_admin.email')))->first();

        $ada = User::factory()->pending()->create([
            'name' => 'Ada Okonkwo',
            'email' => 'ada@minimini.test',
            'phone' => '+2348030000001',
            'country' => 'Nigeria',
            'referral_code' => 'ADA4GOLD',
        ]);

        $this->confirmPayment($ada, PaymentType::Initial);

        $ben = User::factory()->pending()->create([
            'name' => 'Ben Adeyemi',
            'email' => 'ben@minimini.test',
            'phone' => '+2348030000002',
            'country' => 'Nigeria',
            'referral_code' => 'BEN4GOLD',
        ]);

        app(AttachReferral::class)->handle($ben, 'ADA4GOLD');
        $this->confirmPayment($ben->fresh(), PaymentType::Initial);

        $chioma = User::factory()->pending()->create([
            'name' => 'Chioma Nwosu',
            'email' => 'chioma@minimini.test',
            'phone' => '+2348030000003',
            'country' => 'Nigeria',
            'referral_code' => 'CHIOMA01',
        ]);

        app(AttachReferral::class)->handle($chioma, 'ADA4GOLD');

        Announcement::query()->updateOrCreate(
            ['title' => 'October asset note'],
            [
                'created_by' => $admin?->id,
                'body' => 'The cooperative continues to allocate new member funds across real estate, allocated gold, and energy holdings. Unit purchases confirmed this month are already on the ledger.',
                'is_published' => true,
                'published_at' => now()->subDay(),
            ],
        );

        Meeting::query()->updateOrCreate(
            ['title' => 'Monthly members\' circle'],
            [
                'created_by' => $admin?->id,
                'description' => 'A short briefing on purchases, unit price, and questions from members.',
                'location' => 'Online',
                'meeting_url' => 'https://meet.minimini.org/circle',
                'starts_at' => now()->addDays(10)->setTime(16, 0),
                'ends_at' => now()->addDays(10)->setTime(17, 0),
                'status' => MeetingStatus::Scheduled,
            ],
        );

        Banner::query()->updateOrCreate(
            ['position' => 'home_hero', 'title' => 'Pool together. Hold real assets.'],
            [
                'subtitle' => 'Members buy units. The cooperative puts that capital into real estate, gold, oil, and other stable holdings.',
                'image_path' => null,
                'link_url' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'About',
                'excerpt' => 'A member-owned cooperative for stable assets.',
                'body' => "minimini.org is a cooperative network. Members contribute money, receive units, and share in a pool that is invested in real estate, gold, oil, and other assets meant to hold value.\n\nA person who registers is pending until the first payment is confirmed by the payment provider. Only then does the account become active and the units land on an append-only ledger.",
                'is_published' => true,
                'meta_title' => 'About minimini.org',
                'meta_description' => 'How the minimini.org cooperative works.',
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'how-it-works'],
            [
                'title' => 'How it works',
                'excerpt' => 'Register, pay, and hold units.',
                'body' => "1. Create an account. You can include a referral code from another member.\n2. Pay at least the minimum in USD. The unit price at that moment is stored on the payment.\n3. Units credited equal the amount paid divided by that snapshotted price, rounded down to a whole unit.\n4. When a person you referred becomes active, you receive the referral bonus once.\n5. Active members can buy more units whenever they want.",
                'is_published' => true,
                'meta_title' => 'How minimini.org works',
                'meta_description' => 'Registration, payments, units, and referrals.',
            ],
        );
    }

    private function confirmPayment(User $user, PaymentType $type): void
    {
        $txRef = 'MM'.$user->id.'-'.Str::ulid();

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'tx_ref' => $txRef,
            'amount_usd' => '10.00',
            'unit_price_snapshot' => '0.010000',
            'units_purchased' => 1000,
            'type' => $type,
            'status' => PaymentStatus::Pending,
        ]);

        $raw = '{"status":"success","data":{"id":'.$user->id.',"tx_ref":"'.$txRef.'","amount":"10.00","currency":"USD","status":"successful"}}';

        app(VerifyPayment::class)->handle($payment, new PaymentVerification(
            successful: true,
            transactionId: (string) $user->id,
            txRef: $txRef,
            amount: '10.00',
            currency: 'USD',
            rawBody: $raw,
        ));
    }
}
