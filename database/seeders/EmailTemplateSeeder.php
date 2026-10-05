<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Cms\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'welcome_pending',
                'name' => 'Welcome (pending)',
                'subject' => 'Welcome to {{site_name}}',
                'body' => "Hello {{name}},\n\nYour account is pending activation. Complete payment to unlock your member area.\n\n— {{site_name}}",
            ],
            [
                'key' => 'payment_receipt',
                'name' => 'Payment receipt',
                'subject' => 'Payment confirmed · {{tx_ref}}',
                'body' => "Hello {{name}},\n\nWe confirmed your payment of {{amount_usd}} USD ({{units}} units).\nReference: {{tx_ref}}\n\n— {{site_name}}",
            ],
            [
                'key' => 'announcement',
                'name' => 'Announcement',
                'subject' => '{{title}}',
                'body' => "{{body}}\n\n— {{site_name}}",
            ],
            [
                'key' => 'outbound_bulk',
                'name' => 'Bulk outbound',
                'subject' => '{{subject}}',
                'body' => "{{body}}\n\n— {{site_name}}",
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::query()->updateOrCreate(
                ['key' => $template['key']],
                [...$template, 'is_active' => true],
            );
        }
    }
}
