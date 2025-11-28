<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('retailer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('JOD');
            $table->enum('payment_method', ['cliq_qr', 'jomopay', 'apple_pay', 'google_pay', 'card', 'cash']);
            $table->string('gateway', 50)->nullable(); // 'cliq', 'jomopay', 'stripe'
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('transaction_id')->unique()->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['retailer_id', 'status']);
            $table->index('order_id');
            $table->index('gateway');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
