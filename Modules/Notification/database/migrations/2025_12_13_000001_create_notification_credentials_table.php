<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_credentials')) {
            return;
        }

        Schema::create('notification_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->unique(); // smtp, sms, whatsapp, push
            $table->string('provider'); // smtp, twilio, meta, firebase, etc.
            $table->json('credentials'); // Encrypted credentials storage
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_result')->nullable(); // success, error
            $table->text('last_test_message')->nullable();
            $table->timestamps();

            $table->index(['channel', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_credentials');
    }
};
