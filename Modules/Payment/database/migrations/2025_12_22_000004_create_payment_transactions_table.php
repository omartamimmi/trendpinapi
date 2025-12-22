<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Reference
            $table->string('reference', 50)->unique(); // PAY-XXXXX-TIMESTAMP
            $table->unsignedBigInteger('qr_session_id')->nullable();

            // Parties involved
            $table->unsignedBigInteger('customer_id'); // User who paid
            $table->unsignedBigInteger('brand_id'); // Retailer brand
            $table->unsignedBigInteger('branch_id'); // Specific branch

            // Amount details
            $table->decimal('original_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->string('currency', 3)->default('JOD');

            // Fee calculation (for analytics)
            $table->decimal('gateway_fee', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2); // Amount after all fees

            // Bank offer info
            $table->unsignedBigInteger('bank_offer_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('discount_type', 50)->nullable(); // percentage, fixed, cashback
            $table->decimal('discount_value', 10, 2)->nullable();

            // Payment method
            $table->string('payment_method', 50); // card, apple_pay, google_pay, cliq
            $table->string('gateway', 50); // tap, hyperpay, paytabs, cliq

            // Card details (for card payments)
            $table->unsignedBigInteger('tokenized_card_id')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->string('card_brand', 30)->nullable();
            $table->string('card_bin', 8)->nullable();

            // Gateway response
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_charge_id')->nullable();
            $table->string('gateway_authorization_code')->nullable();
            $table->json('gateway_response')->nullable();

            // 3DS / Authentication
            $table->boolean('requires_3ds')->default(false);
            $table->string('auth_url')->nullable();
            $table->string('auth_status')->nullable(); // authenticated, attempted, failed

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'authorized',
                'captured',
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'partially_refunded'
            ])->default('pending');

            // Refund tracking
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();

            // Error tracking
            $table->string('failure_code')->nullable();
            $table->string('failure_message')->nullable();
            $table->text('failure_details')->nullable();

            // Customer details (for records)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_ip')->nullable();
            $table->string('customer_device')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('qr_session_id')->references('id')->on('qr_payment_sessions')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('bank_offer_id')->references('id')->on('bank_offers')->nullOnDelete();
            $table->foreign('bank_id')->references('id')->on('banks')->nullOnDelete();
            $table->foreign('tokenized_card_id')->references('id')->on('tokenized_cards')->nullOnDelete();

            // Indexes
            $table->index('reference');
            $table->index('gateway_transaction_id');
            $table->index(['customer_id', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_method', 'created_at']);
            $table->index(['gateway', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
