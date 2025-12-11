<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_deliveries')) {

            Schema::create('notification_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_message_id')->constrained('notification_messages')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('channel'); // push, sms, email
                $table->foreignId('provider_id')->nullable()->constrained('notification_providers')->nullOnDelete();
                $table->string('status')->default('pending'); // pending, sent, delivered, failed, read, clicked
                $table->text('provider_response')->nullable();
                $table->string('provider_message_id')->nullable();
                $table->text('failed_reason')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('clicked_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['notification_message_id', 'channel']);
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
