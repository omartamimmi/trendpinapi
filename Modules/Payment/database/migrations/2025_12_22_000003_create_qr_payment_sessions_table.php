<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_payment_sessions', function (Blueprint $table) {
            $table->id();

            // Unique identifiers
            $table->string('session_code', 20)->unique(); // e.g., "TRP-ABC123XYZ"
            $table->text('qr_code_data'); // QR code content (URL)
            $table->longText('qr_code_image')->nullable(); // Base64 encoded QR image

            // Retailer info (who created)
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by_user_id');

            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('JOD');
            $table->string('description', 255)->nullable();
            $table->string('reference')->nullable(); // External reference

            // Customer info (filled after scan)
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamp('scanned_at')->nullable();

            // Discount info (calculated after card selection)
            $table->decimal('original_amount', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('bank_offer_id')->nullable();

            // Payment processing
            $table->unsignedBigInteger('transaction_id')->nullable(); // Link to payment_transactions
            $table->string('gateway', 50)->nullable(); // tap, hyperpay, paytabs, cliq
            $table->string('gateway_transaction_id', 255)->nullable();
            $table->string('payment_method', 50)->nullable(); // card, apple_pay, google_pay, cliq

            // Status tracking
            $table->enum('status', [
                'pending',      // Created, waiting for customer
                'scanned',      // Customer scanned QR
                'processing',   // Payment in progress
                'completed',    // Payment successful
                'failed',       // Payment failed
                'expired',      // Session expired
                'cancelled'     // Manually cancelled
            ])->default('pending');

            // Error tracking
            $table->string('failure_reason')->nullable();
            $table->text('failure_details')->nullable();

            // Expiry
            $table->timestamp('expires_at');

            // Timestamps
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('bank_offer_id')->references('id')->on('bank_offers')->nullOnDelete();

            // Indexes
            $table->index('session_code');
            $table->index(['brand_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('expires_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_payment_sessions');
    }
};
