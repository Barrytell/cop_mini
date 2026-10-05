<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('country');
            $table->text('kin_name')->nullable()->after('avatar_path');
            $table->text('kin_relationship')->nullable()->after('kin_name');
            $table->text('kin_phone')->nullable()->after('kin_relationship');
            $table->text('payout_bank_name')->nullable()->after('kin_phone');
            $table->text('payout_account_name')->nullable()->after('payout_bank_name');
            $table->text('payout_account_number')->nullable()->after('payout_account_name');
            $table->text('payout_routing_code')->nullable()->after('payout_account_number');
            $table->boolean('notify_payments')->default(true)->after('payout_routing_code');
            $table->boolean('notify_announcements')->default(true)->after('notify_payments');
            $table->boolean('notify_meetings')->default(true)->after('notify_announcements');
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['user_id', 'announcement_id']);
        });

        Schema::create('meeting_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('attending')->default(true);
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('status', 20)->default('open')->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['support_ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('meeting_rsvps');
        Schema::dropIfExists('announcement_reads');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_path',
                'kin_name',
                'kin_relationship',
                'kin_phone',
                'payout_bank_name',
                'payout_account_name',
                'payout_account_number',
                'payout_routing_code',
                'notify_payments',
                'notify_announcements',
                'notify_meetings',
            ]);
        });
    }
};
