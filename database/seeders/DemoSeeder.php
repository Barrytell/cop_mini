<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MeetingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Cms\Models\Banner;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Payments\Actions\ConfirmPayment;
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

        $slides = [
            ['home_hero', 'Pool together. Hold real assets.', 'Members buy units in real estate, gold, oil, and other stable holdings.', 'images/banners/estate.svg', 1],
            ['home_hero', 'Allocated gold', 'A share of the pool is held in allocated gold.', 'images/banners/gold.svg', 2],
            ['home_hero', 'Energy holdings', 'Oil and other energy positions sit beside property.', 'images/banners/energy.svg', 3],
            ['member_dashboard', 'Your units, your pool', 'Buy more units whenever you are ready.', 'images/banners/estate.svg', 1],
            ['member_dashboard', 'Bring another member', 'The referral bonus posts once their first payment is confirmed.', 'images/banners/gold.svg', 2],
        ];

        foreach ($slides as [$position, $title, $subtitle, $image, $order]) {
            Banner::query()->updateOrCreate(
                ['position' => $position, 'title' => $title],
                [
                    'subtitle' => $subtitle,
                    'image_path' => $image,
                    'link_url' => null,
                    'sort_order' => $order,
                    'is_active' => true,
                ],
            );
        }

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

        app(ConfirmPayment::class)->handle($payment, new PaymentVerification(
            successful: true,
            transactionId: (string) $user->id,
            txRef: $txRef,
            amount: '10.00',
            currency: 'USD',
            rawBody: $raw,
        ));
    }
}
