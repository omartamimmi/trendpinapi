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
        if (!Schema::hasTable('qr_payments')) {

            Schema::create('qr_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('merchant_id')->constrained('users')->cascadeOnDelete(); // Retailer who generated QR
                $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete(); // Customer who paid
                $table->string('qr_code_reference')->unique(); // Unique reference for the QR code
                $table->decimal('amount', 10, 2); // Payment amount
                $table->string('currency', 3)->default('JOD');
                $table->text('description')->nullable(); // Payment description
                $table->enum('status', ['pending', 'completed', 'expired', 'cancelled'])->default('pending');
                $table->text('qr_code_data')->nullable(); // Encrypted QR code payload
                $table->string('qr_code_image')->nullable(); // Path to QR code image
                $table->timestamp('expires_at'); // QR code expiry time
                $table->timestamp('paid_at')->nullable(); // When payment was completed
                $table->json('metadata')->nullable(); // Additional data (order_id, etc.)
                $table->timestamps();

                $table->index(['merchant_id', 'status']);
                $table->index(['customer_id', 'status']);
                $table->index('status');
                $table->index('expires_at');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_payments');
    }
};
