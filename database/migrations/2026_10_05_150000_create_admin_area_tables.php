<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable();
            $table->text('totp_secret')->nullable();
            $table->timestamp('totp_confirmed_at')->nullable();
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('audience')->default('all');
            $table->json('audience_user_ids')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('scheduled_for')->nullable();
            $table->boolean('send_email')->default(false);
            $table->boolean('send_notification')->default(true);
            $table->string('attachment_path')->nullable();
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
        });

        Schema::create('setting_changes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_usd', 12, 2);
            $table->string('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('flutterwave');
            $table->string('event')->nullable();
            $table->string('tx_ref')->nullable()->index();
            $table->unsignedTinyInteger('http_status')->default(200);
            $table->boolean('signature_valid')->default(false);
            $table->longText('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->string('location')->default('footer');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('outbound_messages', function (Blueprint $table) {
            $table->id();
            $table->string('channel');
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('audience')->default('all');
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->string('status')->default('queued');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_messages');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('setting_changes');

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'read_at']);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn([
                'audience',
                'audience_user_ids',
                'is_pinned',
                'scheduled_for',
                'send_email',
                'send_notification',
                'attachment_path',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['permissions', 'totp_secret', 'totp_confirmed_at']);
        });
    }
};
