<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Cms\Models\Download;
use App\Modules\Cms\Models\Faq;
use App\Modules\Cms\Models\GalleryItem;
use App\Modules\Cms\Models\Page;
use App\Modules\Cms\Models\Post;
use App\Modules\Cms\Models\PostCategory;
use App\Modules\Cms\Models\TeamMember;
use App\Modules\Cms\Models\Testimonial;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PublicContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->artwork();
        $this->pages();
        $this->stories();
        $this->faqs();
        $this->voices();
        $this->team();
        $this->gallery();
        $this->documents();

        if (config('minimini.seed_demo_data')) {
            app(SettingsService::class)->put('whatsapp_number', '+2348090001122');
        }
    }

    private function pages(): void
    {
        $pages = [
            ['about', 'About us', 'standard', true, 'about', 10, 'A member-owned pool for assets that are meant to last.', 'About the cooperative', 'Who owns minimini.org and how contributions become units.', $this->about()],
            ['mission', 'Mission and vision', 'standard', false, 'about', 20, 'Hold real assets together, and keep every unit on the record.', 'Mission and vision', 'The purpose of the cooperative and the standard it holds itself to.', $this->mission()],
            ['leadership', 'Leadership', 'team', false, 'about', 30, 'The people who watch the pool, the ledger, and the membership.', 'Leadership', 'The board and officers of the cooperative.', 'The board sets the unit price, approves asset allocations, and publishes what members are entitled to see. Officers do not edit a payment after it has been confirmed.'],
            ['how-it-works', 'How it works', 'standard', true, 'membership', 40, 'Register, pay, become a member, and hold units.', 'How membership works', 'The four steps from a new account to an active member.', $this->how()],
            ['investments', 'Investment areas', 'investments', true, 'invest', 50, 'Property, gold, energy, and other holdings chosen for steadiness.', 'Investment areas', 'Where member contributions are put to work.', "The pool is not a single bet. Contributions are spread across asset areas the board has named in advance.\n\n## How an allocation is chosen\nThe board publishes the area, the reason, and the share of new contributions it expects to place there. A member's units stay on the ledger either way. The mix can change. Past payments do not."],
            ['real-estate', 'Real estate', 'asset', false, 'invest', 51, 'Income property and land held for the long term.', 'Real estate', 'Property holdings inside the cooperative pool.', "Completed buildings with tenants, and land the board expects to hold rather than trade.\n\n## What members should expect\nProperty can sit illiquid for years. Rent, costs, and local rules move the value. Units do not give a member the deed to a single flat."],
            ['gold', 'Gold', 'asset', false, 'invest', 52, 'Allocated metal, held to steady the rest of the pool.', 'Gold', 'How gold sits beside property and energy.', "A share of the pool is aimed at allocated gold, the kind that can be identified rather than promised on paper alone.\n\n## Why it is here\nGold does not pay rent. It is here so the pool is not only buildings and barrels. The price still moves, sometimes sharply."],
            ['oil-energy', 'Oil and energy', 'asset', false, 'invest', 53, 'Energy holdings kept as a sleeve, not the whole pool.', 'Oil and energy', 'Energy positions inside the cooperative.', "Oil and related energy positions are a sleeve of the pool. They are sized so a shock in that market does not become the whole story.\n\n## The limit\nEnergy prices swing. The board states the share it is willing to place there and does not silently expand it."],
            ['other-assets', 'Other stable assets', 'asset', false, 'invest', 54, 'Holdings chosen because they are meant to keep their value.', 'Other stable assets', 'Assets that sit beside property, gold, and energy.', "From time to time the board adds a holding that is meant to be steadier than a trading position: for example a short-dated instrument or a store of value the members have already discussed.\n\n## The test\nIf an asset needs a story about a quick gain, it does not belong in this sleeve."],
            ['membership', 'Membership and units', 'membership', true, 'membership', 60, 'A unit is the record of what you paid, at the price that day.', 'Membership and units', 'How the unit price, the minimum payment, and the ledger work.', $this->membership()],
            ['referral-program', 'Referral program', 'referral', false, 'membership', 70, 'Invite someone. The bonus posts when they become active.', 'Referral program', 'When the referral bonus is paid, and when it is not.', $this->referral()],
            ['terms', 'Terms and conditions', 'legal', false, 'legal', 80, 'The rules for an account, a payment, and a unit.', 'Terms and conditions', 'Membership terms for minimini.org.', $this->terms()],
            ['privacy', 'Privacy policy', 'legal', false, 'legal', 90, 'What we keep, why we keep it, and who can see it.', 'Privacy policy', 'How the cooperative handles member and visitor information.', $this->privacy()],
            ['risk-disclosure', 'Risk disclosure', 'legal', false, 'legal', 100, 'Units can lose value. Read this before you pay.', 'Risk disclosure', 'The risks of contributing to the cooperative pool.', $this->risk()],
        ];

        foreach ($pages as [$slug, $title, $template, $nav, $group, $order, $excerpt, $metaTitle, $metaDescription, $body]) {
            Page::query()->updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'template' => $template,
                'excerpt' => $excerpt,
                'body' => $body,
                'is_published' => true,
                'show_in_nav' => $nav,
                'nav_group' => $group,
                'sort_order' => $order,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
            ]);
        }
    }

    private function stories(): void
    {
        $notes = PostCategory::query()->updateOrCreate(['slug' => 'asset-notes'], [
            'name' => 'Asset notes',
            'description' => 'How new contributions were placed.',
        ]);
        $membership = PostCategory::query()->updateOrCreate(['slug' => 'membership'], [
            'name' => 'Membership',
            'description' => 'Payments, units, and the ledger.',
        ]);
        $meetings = PostCategory::query()->updateOrCreate(['slug' => 'meetings'], [
            'name' => 'Meetings',
            'description' => 'What the membership gathered to hear.',
        ]);

        $posts = [
            [$notes, 'Where this month\'s contributions went', 'october-allocations', 'images/gallery/estate.webp', 'New units this month were aimed at occupied property, allocated gold, and a smaller energy sleeve.', "The board reviewed the confirmed payments for the month and placed them in the published mix.\n\n## The split\nMost of the new money followed occupied property. A smaller share went to allocated gold. Energy stayed inside the limit already stated to members.\n\n## What did not change\nOlder payments kept the unit price stored on the day they were confirmed. This note does not recalculate them."],
            [$membership, 'Your first payment is the start of membership', 'first-payment', 'images/gallery/gold.webp', 'An account stays pending until Flutterwave confirms the first charge. Units are credited from that confirmation, not from the signup.', "Creating an account reserves a referral code and a temporary member number. It does not buy units.\n\n## What confirmation means\nThe cooperative asks Flutterwave for the charge, checks the amount and the currency, and only then writes the ledger row. A page that says successful in the browser is not enough on its own.\n\n## After that\nThe member number changes from a temporary code to a permanent one, and further purchases are top-ups on the same ledger."],
            [$meetings, 'What we cover in the members\' circle', 'members-circle', 'images/gallery/energy.webp', 'The monthly circle is a briefing: price, new allocations, and questions. It is not a trading call.', "The circle is short on purpose. Members hear the current unit price, the allocations made since the last meeting, and anything the board needs to say in public.\n\n## How to attend\nThe date, time, and link are on the events page. Active members can also mark attendance from their account.\n\n## What it is not\nNobody is asked to approve a trade in the room. Decisions about the pool are recorded by the board and then reported."],
        ];

        foreach ($posts as [$category, $title, $slug, $cover, $excerpt, $body]) {
            Post::query()->updateOrCreate(['slug' => $slug], [
                'post_category_id' => $category->id,
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $body,
                'cover_path' => $cover,
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'meta_title' => $title,
                'meta_description' => $excerpt,
            ]);
        }
    }

    private function faqs(): void
    {
        $items = [
            ['When do I become a member?', 'When your first payment is confirmed by Flutterwave on our server. Until then the account stays pending and no units are credited.'],
            ['How are units calculated?', 'Units equal the USD amount of the payment divided by the unit price stored on that payment, rounded down to a whole unit. A later price change does not rewrite it.'],
            ['Can I buy more units later?', 'Yes, once the account is active. Each purchase is its own payment with its own price snapshot.'],
            ['When is the referral bonus paid?', 'Once, when the person you invited makes a confirmed first payment. Creating an account does not pay the bonus, and a second payment does not pay it again.'],
            ['Can I use my own referral link?', 'No. Your own code is ignored, and the system will not attach you to yourself.'],
            ['Are returns guaranteed?', 'No. Units record what you contributed. The value of the pool can fall. Read the risk disclosure before you pay.'],
            ['What details are encrypted?', 'Next-of-kin and payout details on your profile are encrypted at rest. Your name, email, and payment history are stored so the cooperative can operate the account.'],
            ['How do I reach the office?', 'Use the contact form. The message is saved and emailed to the address in the site settings.'],
        ];

        foreach ($items as $index => [$question, $answer]) {
            Faq::query()->updateOrCreate(['question' => $question], [
                'answer' => $answer,
                'sort_order' => ($index + 1) * 10,
                'is_published' => true,
            ]);
        }
    }

    private function voices(): void
    {
        $notes = [
            ['Ada Okonkwo', 'Active member', 'Lagos', 'I could see the unit price on the payment itself. When the price later moved, my first purchase stayed at the old figure.'],
            ['Ben Adeyemi', 'Active member', 'Accra', 'The bonus arrived after my first payment, not when I signed up. That was the rule I had been told, and it matched the ledger.'],
            ['Chioma Nwosu', 'Pending member', 'Abuja', 'The account was clear about staying pending until the payment was confirmed. I was not shown units I had not paid for.'],
            ['Ifeanyi Cole', 'Active member', 'London', 'The referral link was something I could send once. I did not have to explain a second bonus that never existed.'],
        ];

        foreach ($notes as $index => [$name, $role, $location, $quote]) {
            Testimonial::query()->updateOrCreate(['name' => $name], [
                'role' => $role,
                'location' => $location,
                'quote' => $quote,
                'sort_order' => ($index + 1) * 10,
                'is_published' => true,
            ]);
        }
    }

    private function team(): void
    {
        $people = [
            ['Ngozi Adeyemi', 'Chair', 'images/team/ngozi.webp', 'Ngozi chairs the board. She signs off the unit price and the published asset mix, and she does not have a tool that rewrites a confirmed payment.'],
            ['Tunde Bakare', 'Treasurer', 'images/team/tunde.webp', 'Tunde reconciles Flutterwave confirmations to the ledger. A charge that does not match the quoted amount is not credited.'],
            ['Amina Diallo', 'Asset steward', 'images/team/amina.webp', 'Amina prepares the notes members read: what was bought, in which sleeve, and what was left unallocated.'],
            ['Samuel Okeke', 'Membership secretary', 'images/team/samuel.webp', 'Samuel watches pending accounts, meetings, and the referral record. A bonus is proposed only after a first payment is confirmed.'],
        ];

        foreach ($people as $index => [$name, $role, $image, $bio]) {
            TeamMember::query()->updateOrCreate(['name' => $name], [
                'role' => $role,
                'bio' => $bio,
                'image_path' => $image,
                'sort_order' => ($index + 1) * 10,
                'is_published' => true,
            ]);
        }
    }

    private function gallery(): void
    {
        $items = [
            ['estate.webp', 'Occupied property', 'Residential buildings the pool is built to hold, not to flip.'],
            ['gold.webp', 'Allocated gold', 'Metal held so the pool is not only property and energy.'],
            ['energy.webp', 'Energy sleeve', 'A limited share of the pool, stated to members in advance.'],
            ['ledger.webp', 'The ledger', 'Each unit is a row. The balance is the sum of the rows.'],
        ];

        foreach ($items as $index => [$file, $title, $caption]) {
            GalleryItem::query()->updateOrCreate(['title' => $title], [
                'caption' => $caption,
                'image_path' => 'images/gallery/'.$file,
                'sort_order' => ($index + 1) * 10,
                'is_published' => true,
            ]);
        }
    }

    private function documents(): void
    {
        $files = [
            ['membership-guide.pdf', 'Membership guide', 'How an account becomes active and how units are counted.'],
            ['risk-summary.pdf', 'Risk summary', 'A short statement of what can go wrong with the pool.'],
        ];

        foreach ($files as $index => [$name, $title, $description]) {
            $path = public_path('files/'.$name);
            File::ensureDirectoryExists(dirname($path));
            $pdf = $this->pdf($title.'. '.$description);
            File::put($path, $pdf);

            Download::query()->updateOrCreate(['title' => $title], [
                'description' => $description,
                'file_path' => 'files/'.$name,
                'file_size' => strlen($pdf),
                'sort_order' => ($index + 1) * 10,
                'is_published' => true,
            ]);
        }
    }

    private function artwork(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $this->mark(public_path('favicon-32.png'), 32);
        $this->mark(public_path('icons/apple-touch-icon.png'), 180);
        $this->mark(public_path('icons/icon-192.png'), 192);
        $this->mark(public_path('icons/icon-512.png'), 512);
        $this->scene(public_path('images/og.jpg'), 1200, 630, 'jpg', 'Pool together.');
        $this->scene(public_path('images/gallery/estate.webp'), 800, 600, 'webp', 'Property');
        $this->scene(public_path('images/gallery/gold.webp'), 800, 600, 'webp', 'Gold');
        $this->scene(public_path('images/gallery/energy.webp'), 800, 600, 'webp', 'Energy');
        $this->scene(public_path('images/gallery/ledger.webp'), 800, 600, 'webp', 'Ledger');
        $this->portrait(public_path('images/team/ngozi.webp'), 'NA');
        $this->portrait(public_path('images/team/tunde.webp'), 'TB');
        $this->portrait(public_path('images/team/amina.webp'), 'AD');
        $this->portrait(public_path('images/team/samuel.webp'), 'SO');
    }

    private function mark(string $path, int $size): void
    {
        File::ensureDirectoryExists(dirname($path));
        $image = imagecreatetruecolor($size, $size);
        imagealphablending($image, true);
        $navy = imagecolorallocate($image, 11, 31, 58);
        $gold = imagecolorallocate($image, 196, 163, 90);
        imagefilledrectangle($image, 0, 0, $size, $size, $navy);
        $pad = (int) max(2, $size * 0.22);
        imagefilledrectangle($image, $pad, $pad, $size - $pad, $size - $pad, $gold);
        imagepng($image, $path);
        imagedestroy($image);
    }

    private function scene(string $path, int $width, int $height, string $type, string $label): void
    {
        File::ensureDirectoryExists(dirname($path));
        $image = imagecreatetruecolor($width, $height);
        $navy = imagecolorallocate($image, 11, 31, 58);
        $gold = imagecolorallocate($image, 196, 163, 90);
        $cream = imagecolorallocate($image, 246, 243, 238);
        imagefilledrectangle($image, 0, 0, $width, $height, $navy);
        imagefilledrectangle($image, 0, (int) ($height * 0.72), $width, $height, $gold);
        imagestring($image, 5, 24, 24, $label, $cream);
        if ($type === 'webp' && function_exists('imagewebp')) {
            imagewebp($image, $path, 80);
        } else {
            imagejpeg($image, $path, 82);
        }
        imagedestroy($image);
    }

    private function portrait(string $path, string $initials): void
    {
        File::ensureDirectoryExists(dirname($path));
        $image = imagecreatetruecolor(256, 256);
        $navy = imagecolorallocate($image, 22, 48, 79);
        $gold = imagecolorallocate($image, 212, 188, 125);
        imagefilledrectangle($image, 0, 0, 256, 256, $navy);
        imagestring($image, 5, 110, 120, $initials, $gold);
        if (function_exists('imagewebp')) {
            imagewebp($image, $path, 82);
        } else {
            imagejpeg($image, preg_replace('/\.webp$/', '.jpg', $path), 82);
        }
        imagedestroy($image);
    }

    private function pdf(string $text): string
    {
        $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 16 Tf 72 720 Td ({$safe}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function about(): string
    {
        return "minimini.org is a cooperative. Members contribute money, receive units, and share a pool that is invested in real estate, gold, oil, and other assets chosen because they are meant to hold value.\n\n## Who it is for\nPeople who would rather hold a recorded share of a pool than chase a trade. You can see the price you paid, the units that price produced, and the referrals that have been rewarded.\n\n## What an account is\nA new account is pending. It becomes active when the first payment is confirmed. The member number is issued at that moment, and the units are written to a ledger that is not edited afterward.";
    }

    private function mission(): string
    {
        return "The mission is to let members pool contributions into assets that are meant to last, and to keep a record that a later price change cannot rewrite.\n\n## Vision\nA membership that can point to a ledger, a published asset mix, and a meeting, and understand all three without a private explanation.\n\n## The standard\n- Confirm every payment with the provider before crediting units.\n- Pay a referral bonus once, and only after the invited person is active.\n- Say plainly when a return is not guaranteed.";
    }

    private function how(): string
    {
        return "## Register\nCreate an account with your name, email, phone, and country. If you arrived from an invite, the referral code is already attached.\n\n## Pay\nChoose an amount of at least the minimum. Flutterwave hosts the checkout. We verify the amount, the currency, and the reference before anything is credited.\n\n## Become a member\nA confirmed first payment changes the account from pending to active, issues the member number, and writes the units.\n\n## Earn returns\nActive members can buy more units. The cooperative places contributions into the published asset areas. Returns depend on those assets and are not promised.";
    }

    private function membership(): string
    {
        return "A unit is not a bank balance. It is the whole number of shares your confirmed USD payment bought at the price stored on that payment.\n\n## The live price\nThe price on this page is the price a new payment would use. It comes from the cooperative settings. Changing it affects future payments only.\n\n## The minimum\nThe first payment, and later top-ups, must meet the minimum shown beside the price.\n\n## The ledger\nEach credit is a row: a purchase, a referral bonus, or an adjustment. Your balance is the sum. Rows are not edited and are not deleted.";
    }

    private function referral(): string
    {
        return "Every member has a code and a link. Someone who opens that link keeps the code for 30 days, even if they browse the site before they register.\n\n## When you are paid\nThe bonus is credited when their first payment is confirmed. The amount is the referral bonus in settings at that moment.\n\n## When you are not paid\n- They only create an account.\n- They use your link but you are the same person.\n- Their payment was already confirmed and the bonus was already written.\n\nThe bonus is one ledger row, tied to that member, and it is not repeated.";
    }

    private function terms(): string
    {
        return "These terms cover an account on this website. Paying the first confirmed charge is what makes the account a membership.\n\n## Accounts\nYou must give a name, email, phone, and country that are yours. One person does not hold two memberships to collect two referral bonuses for the same payment.\n\n## Payments\nCheckout is hosted by Flutterwave. We credit units only after a server-side confirmation that the amount and currency match the quote. You are responsible for charges your bank or the provider adds.\n\n## Units\nUnits are calculated from the USD amount and the snapshotted price, rounded down. They record a contribution. They are not a deposit and they are not redeemable on demand unless the board publishes a distribution.\n\n## Conduct\nDo not use another member's identity, your own referral code, or a payment you do not intend to complete.";
    }

    private function privacy(): string
    {
        return "We keep what we need to run a membership: name, email, phone, country, payments, units, referrals, and messages you send.\n\n## Payments\nFlutterwave processes the card, transfer, or mobile-money charge. We store the reference, the amounts, the currency, and the verification we received.\n\n## Sensitive profile fields\nNext-of-kin and payout details are encrypted at rest. They are used if the cooperative pays a distribution.\n\n## Cookies\nThe site stores a referral code for 30 days when you arrive with an invite, a session cookie so you can stay signed in, and a preference that hides the cookie notice. We do not sell these.\n\n## Contact\nA message from the contact form is saved and emailed to the office address in settings. Write to that address to ask what we hold about you.";
    }

    private function risk(): string
    {
        return "Read this before you pay. Contributing to the pool can lose money.\n\n## Asset risk\nProperty can be vacant or hard to sell. Gold and energy prices move. A holding described as stable can still fall.\n\n## Liquidity\nUnits are not a cash balance you can withdraw on the day you ask. A distribution happens only when the board declares one.\n\n## Operational risk\nA payment provider, a bank, or this website can be unavailable. A confirmation that fails is not credited. A confirmation that succeeds is credited once.\n\n## No promise of profit\nNothing on this website is a forecast of a return. Past allocations are descriptions, not a guarantee that the next one will look the same.";
    }
}
