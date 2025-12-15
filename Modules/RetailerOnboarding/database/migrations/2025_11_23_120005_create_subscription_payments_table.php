<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_payments')) {
            return;
        }

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retailer_subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('discount_code')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'cliq']);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            // Card payment details (encrypted/tokenized in production)
            $table->string('card_last_four', 4)->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
