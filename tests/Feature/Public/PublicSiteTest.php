<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Mail\ContactMessageReceived;
use App\Models\User;
use App\Modules\Cms\Models\Download;
use App\Modules\Cms\Models\Faq;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\Post;
use App\Modules\Cms\Models\PostCategory;
use App\Modules\Meetings\Models\Meeting;
use App\Support\MapEmbed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_explains_how_membership_starts(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Pool together. Hold real assets.')
            ->assertSee('Join now')
            ->assertSee('How it works')
            ->assertSee('Become a member')
            ->assertDontSee('Log out');
    }

    public function test_register_page_names_the_referrer(): void
    {
        $referrer = User::factory()->create(['name' => 'Ada Okonkwo']);

        $this->get('/register?ref='.$referrer->referral_code)
            ->assertOk()
            ->assertSee('Referred by Ada Okonkwo')
            ->assertSee('noindex, follow', false);
    }

    public function test_contact_form_is_saved_and_emailed(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name' => 'Ngozi Adeyemi',
            'email' => 'ngozi@example.com',
            'phone' => '+2348090001122',
            'subject' => 'Membership question',
            'body' => 'How soon after payment do units appear?',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ngozi@example.com',
            'subject' => 'Membership question',
        ]);
        Mail::assertQueued(ContactMessageReceived::class);
    }

    public function test_contact_form_rejects_invalid_and_honeypot_submissions(): void
    {
        $this->post('/contact', [])->assertSessionHasErrors(['name', 'email', 'subject', 'body']);

        $this->post('/contact', [
            'name' => 'Ngozi Adeyemi',
            'email' => 'ngozi@example.com',
            'subject' => 'Membership question',
            'body' => 'How soon after payment do units appear?',
            'website' => 'https://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_news_list_and_detail_are_public(): void
    {
        $category = PostCategory::query()->create([
            'name' => 'Membership',
            'slug' => 'membership',
        ]);
        $post = Post::query()->create([
            'post_category_id' => $category->id,
            'title' => 'Your first payment',
            'slug' => 'first-payment',
            'excerpt' => 'Units start when the payment is confirmed.',
            'body' => "Creating an account does not buy units.\n\n## Confirmation\nWe verify the charge before writing the ledger.",
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        $this->get('/news')->assertOk()->assertSee('Your first payment');
        $this->get('/news/first-payment')->assertOk()->assertSee('We verify the charge');
        $this->get('/news?category=membership')->assertOk()->assertSee('Your first payment');
        $this->get('/news?category=missing')->assertNotFound();

        $post->update(['is_published' => false]);
        $this->get('/news/first-payment')->assertNotFound();
    }

    public function test_membership_page_shows_the_live_unit_price(): void
    {
        Page::factory()->create([
            'title' => 'Membership and units',
            'slug' => 'membership',
            'template' => 'membership',
            'body' => 'A unit records a confirmed payment.',
            'is_published' => true,
        ]);

        $this->get('/membership')
            ->assertOk()
            ->assertSee('$0.01')
            ->assertSee('A unit records a confirmed payment.');
    }

    public function test_unpublished_pages_stay_hidden(): void
    {
        Page::factory()->create([
            'slug' => 'about',
            'is_published' => false,
        ]);

        $this->get('/about')->assertNotFound();
        $this->get('/pages/about')->assertNotFound();
    }

    public function test_library_pages_load(): void
    {
        Faq::query()->create([
            'question' => 'When do I become a member?',
            'answer' => 'When the first payment is confirmed.',
            'sort_order' => 10,
            'is_published' => true,
        ]);

        $this->get('/faq')->assertOk()->assertSee('When do I become a member?');
        $this->get('/events')->assertOk()->assertSee('Events and meetings');
        $this->get('/testimonials')->assertOk();
        $this->get('/gallery')->assertOk();
        $this->get('/downloads')->assertOk();
        $this->get('/contact')->assertOk()->assertSee('openstreetmap.org', false);
    }

    public function test_sitemap_and_robots_are_published(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('xml', (string) $sitemap->headers->get('Content-Type'));
        $sitemap->assertSee(route('home'), false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /member')
            ->assertSee('Sitemap:');
    }

    public function test_missing_pages_use_the_public_error_copy(): void
    {
        $this->get('/not-a-real-page')->assertNotFound()->assertSee('That page is not here');
    }

    public function test_a_signed_in_member_gets_account_links(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Account')
            ->assertSee('Log out')
            ->assertDontSee('href="'.route('login').'"', false);
    }

    public function test_public_events_keep_the_attendance_link_private(): void
    {
        Meeting::factory()->create([
            'title' => 'October circle',
            'meeting_url' => 'https://meet.example.test/secret-room',
        ]);

        $this->get('/events')
            ->assertOk()
            ->assertSee('October circle')
            ->assertDontSee('secret-room', false);
    }

    public function test_downloads_only_link_to_files_on_this_site(): void
    {
        Download::query()->create([
            'title' => 'Outside file',
            'description' => 'Should not leave the site.',
            'file_path' => 'https://evil.example/secret.pdf',
            'file_size' => 12,
            'is_published' => true,
        ]);
        Download::query()->create([
            'title' => 'Membership guide',
            'file_path' => 'files/membership-guide.pdf',
            'file_size' => 1200,
            'is_published' => true,
        ]);

        $this->get('/downloads')
            ->assertOk()
            ->assertDontSee('evil.example', false)
            ->assertSee('files/membership-guide.pdf', false);
    }

    public function test_a_contact_message_is_kept_when_mail_cannot_be_queued(): void
    {
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('queue')->once()->andThrow(new \RuntimeException('smtp down'));

        $this->post('/contact', [
            'name' => 'Ngozi Adeyemi',
            'email' => 'ngozi@example.com',
            'subject' => 'Membership question',
            'body' => 'How soon after payment do units appear?',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ngozi@example.com',
        ]);
    }

    public function test_map_embeds_stay_on_map_hosts(): void
    {
        $this->assertNotNull(MapEmbed::url('https://www.openstreetmap.org/export/embed.html?marker=1'));
        $this->assertNotNull(MapEmbed::url('https://www.google.com/maps/embed?pb=1'));
        $this->assertNull(MapEmbed::url('https://www.google.com/search?q=lagos'));
        $this->assertNull(MapEmbed::url('https://phish.example@www.openstreetmap.org/export/embed.html'));
        $this->assertNull(safe_url('https://user:secret@example.com/path'));
        $this->assertNull(public_file('../.env'));
        $this->assertNull(public_file('https://evil.example/a.pdf'));
    }
}
