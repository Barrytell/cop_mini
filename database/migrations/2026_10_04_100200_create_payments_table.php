<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('tx_ref')->unique();
            $table->string('flw_transaction_id')->nullable()->index();
            $table->decimal('amount_usd', 14, 2);
            $table->char('currency_paid', 3)->nullable();
            $table->decimal('amount_paid', 14, 2)->nullable();
            $table->decimal('unit_price_snapshot', 14, 6);
            $table->unsignedBigInteger('units_purchased');
            $table->string('type', 20);
            $table->string('status', 20)->index();
            $table->json('gateway_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
