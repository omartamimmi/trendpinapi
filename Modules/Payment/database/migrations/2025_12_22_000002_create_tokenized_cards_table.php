<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokenized_cards', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->unsignedBigInteger('user_id');

            // Gateway info
            $table->string('gateway', 50); // tap, hyperpay, paytabs
            $table->string('gateway_token'); // Token from gateway
            $table->string('gateway_customer_id')->nullable(); // Customer ID at gateway

            // Card display info (safe to store)
            $table->string('card_last_four', 4);
            $table->string('card_brand', 30); // visa, mastercard, amex
            $table->string('card_expiry_month', 2);
            $table->string('card_expiry_year', 4);
            $table->string('cardholder_name')->nullable();
            $table->string('nickname', 50)->nullable(); // User's custom name

            // Bank detection
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('card_type_id')->nullable();
            $table->string('bin_prefix', 8)->nullable(); // First 6-8 digits

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);

            // Wallet type (for Apple/Google Pay)
            $table->string('wallet_type', 20)->nullable(); // apple_pay, google_pay, null for regular

            // Usage tracking
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('bank_id')->references('id')->on('banks')->nullOnDelete();
            $table->foreign('card_type_id')->references('id')->on('card_types')->nullOnDelete();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index('gateway_token');
            $table->index('bin_prefix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokenized_cards');
    }
};
