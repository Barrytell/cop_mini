<?php

declare(strict_types=1);

namespace Tests\Feature\Members;

use App\Enums\LedgerType;
use App\Enums\MeetingStatus;
use App\Models\User;
use App\Modules\Announcements\Models\Announcement;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Units\Actions\AppendLedgerEntry;
use App\Notifications\SupportTicketReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MemberAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_active_member_sees_the_dashboard_and_a_pending_member_does_not(): void
    {
        $active = User::factory()->create(['name' => 'Ada Okonkwo']);
        $pending = User::factory()->pending()->create();

        $this->actingAs($active)
            ->get('/member/dashboard')
            ->assertOk()
            ->assertSee('Ada Okonkwo')
            ->assertSee($active->member_no)
            ->assertSee('Buy more units')
            ->assertSee('Copy referral link');

        $this->actingAs($pending)
            ->get('/member/dashboard')
            ->assertRedirect(route('member.activate'));
    }

    public function test_the_referral_center_includes_share_links_and_a_qr_code(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/member/referrals')
            ->assertOk()
            ->assertSee(route('register', ['ref' => $member->referral_code]), false)
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('facebook.com/sharer', false)
            ->assertSee('twitter.com/intent/tweet', false)
            ->assertSee('t.me/share', false)
            ->assertSee('mailto:?subject=', false);

        $this->actingAs($member)
            ->get('/member/referrals/qr')
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('<svg', false);
    }

    public function test_the_units_statement_filters_and_exports_csv_without_formula_injection(): void
    {
        $member = User::factory()->create();
        app(AppendLedgerEntry::class)->handle($member, 12, LedgerType::AdminAdjustment, note: '=cmd');
        app(AppendLedgerEntry::class)->handle($member, 4, LedgerType::ReferralBonus, note: 'Bonus');

        $this->actingAs($member)
            ->get('/member/units?type=admin_adjustment')
            ->assertOk()
            ->assertSee('=cmd')
            ->assertDontSee('Bonus');

        $export = $this->actingAs($member)->get('/member/units/export?type=admin_adjustment');
        $export->assertOk();
        $csv = $export->streamedContent();
        $this->assertStringContainsString("'=cmd", $csv);
        $this->assertStringNotContainsString('Bonus', $csv);
    }

    public function test_profile_details_are_saved_and_payout_fields_stay_encrypted(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->put('/member/profile', [
                'name' => 'Updated Name',
                'phone' => '+15551239999',
                'country' => 'United States',
                'kin_name' => 'Kin Name',
                'kin_relationship' => 'Sibling',
                'kin_phone' => '+15551238888',
                'payout_bank_name' => 'Example Bank',
                'payout_account_name' => 'Updated Name',
                'payout_account_number' => '1234567890',
                'payout_routing_code' => '021000021',
                'notify_announcements' => '1',
            ])
            ->assertRedirect(route('member.profile.edit'));

        $fresh = $member->fresh();
        $this->assertSame('Updated Name', $fresh->name);
        $this->assertSame('1234567890', $fresh->payout_account_number);
        $this->assertSame('Kin Name', $fresh->kin_name);
        $this->assertFalse($fresh->notify_payments);
        $this->assertTrue($fresh->notify_announcements);

        $stored = DB::table('users')->where('id', $member->id)->value('payout_account_number');
        $this->assertIsString($stored);
        $this->assertStringNotContainsString('1234567890', $stored);
    }

    public function test_opening_an_announcement_clears_the_unread_badge(): void
    {
        $member = User::factory()->create();
        $announcement = Announcement::factory()->create(['title' => 'Pool update']);

        $this->actingAs($member)
            ->get('/member/announcements')
            ->assertOk()
            ->assertSee('Pool update')
            ->assertSee('Unread');

        $this->actingAs($member)
            ->get('/member/announcements/'.$announcement->id)
            ->assertOk()
            ->assertSee('Pool update');

        $this->actingAs($member)
            ->get('/member/announcements')
            ->assertOk()
            ->assertDontSee('Unread');
    }

    public function test_a_member_can_rsvp_and_cancel_for_an_upcoming_meeting(): void
    {
        $member = User::factory()->create();
        $meeting = Meeting::factory()->create(['title' => 'October circle']);
        $past = Meeting::factory()->create([
            'title' => 'Last circle',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->subDay()->addHour(),
            'status' => MeetingStatus::Completed,
        ]);

        $this->actingAs($member)
            ->post('/member/meetings/'.$meeting->id.'/rsvp')
            ->assertRedirect(route('member.meetings.index'));

        $this->assertDatabaseHas('meeting_rsvps', [
            'meeting_id' => $meeting->id,
            'user_id' => $member->id,
            'attending' => true,
        ]);

        $this->actingAs($member)
            ->post('/member/meetings/'.$meeting->id.'/rsvp')
            ->assertRedirect(route('member.meetings.index'));

        $this->assertDatabaseHas('meeting_rsvps', [
            'meeting_id' => $meeting->id,
            'user_id' => $member->id,
            'attending' => false,
        ]);

        $this->actingAs($member)
            ->post('/member/meetings/'.$past->id.'/rsvp')
            ->assertForbidden();
    }

    public function test_a_member_ticket_receives_an_admin_reply_in_the_notification_center(): void
    {
        Notification::fake();
        $member = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($member)
            ->post('/member/support', [
                'subject' => 'Missing receipt',
                'body' => 'Please resend the receipt for my first payment.',
            ])
            ->assertRedirect();

        $ticketId = $member->supportTickets()->firstOrFail()->id;

        $this->actingAs($admin)
            ->post('/admin/support/'.$ticketId.'/reply', [
                'body' => 'The receipt is attached to the payment.',
            ])
            ->assertRedirect(route('admin.support.show', $ticketId));

        Notification::assertSentTo($member, SupportTicketReplied::class);

        $this->actingAs($member)
            ->get('/member/support/'.$ticketId)
            ->assertOk()
            ->assertSee('The receipt is attached to the payment.');
    }
}
