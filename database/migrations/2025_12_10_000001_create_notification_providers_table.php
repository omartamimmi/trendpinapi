<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_providers', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // push, sms, email, whatsapp
            $table->string('provider'); // fcm, twilio, sendgrid, onesignal
            $table->string('name'); // Display name
            $table->text('credentials'); // Encrypted JSON with API keys, etc
            $table->boolean('is_active')->default(false);
            $table->integer('priority')->default(1); // 1=primary, 2=fallback
            $table->json('settings')->nullable(); // Rate limits, retry config, etc
            $table->timestamp('last_tested_at')->nullable();
            $table->text('last_test_result')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_providers');
    }
};
