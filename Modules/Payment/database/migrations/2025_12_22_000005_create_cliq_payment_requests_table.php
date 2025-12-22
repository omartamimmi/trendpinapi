<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliq_payment_requests', function (Blueprint $table) {
            $table->id();

            // Link to QR session
            $table->unsignedBigInteger('qr_session_id');

            // CliQ identifiers
            $table->string('request_id', 50)->unique(); // CLIQ-YYYYMMDDHHMMSS-XXXX
            $table->string('jopacc_reference')->nullable(); // Reference from JOPACC

            // Amount
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('JOD');

            // Sender info
            $table->unsignedBigInteger('sender_bank_id');
            $table->string('sender_alias')->nullable(); // Phone number or CliQ ID
            $table->string('sender_name')->nullable();

            // Receiver info
            $table->string('receiver_alias'); // TrendPin merchant alias
            $table->string('receiver_name')->default('TrendPin');

            // Status
            $table->enum('status', [
                'pending',
                'sent_to_bank',
                'waiting_confirmation',
                'completed',
                'failed',
                'expired',
                'cancelled'
            ])->default('pending');

            // Error tracking
            $table->string('failure_reason')->nullable();
            $table->text('failure_details')->nullable();

            // Deep link info
            $table->text('deep_link')->nullable();
            $table->text('universal_link')->nullable();

            // Timestamps
            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('qr_session_id')->references('id')->on('qr_payment_sessions')->cascadeOnDelete();
            $table->foreign('sender_bank_id')->references('id')->on('banks');

            // Indexes
            $table->index('request_id');
            $table->index('jopacc_reference');
            $table->index(['qr_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliq_payment_requests');
    }
};
