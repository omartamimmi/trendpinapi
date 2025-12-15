<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_settings')) {
            return;
        }

        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // e.g., 'new_customer', 'retailer_approved'
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // e.g., 'Customer', 'Retailer', 'Subscription'
            $table->boolean('is_enabled')->default(true);
            $table->json('recipients'); // ['admin', 'retailer', 'customer']
            $table->json('channels'); // {email: true, sms: false, ...}
            $table->json('templates'); // {admin: {email: {...}, sms: {...}}, ...}
            $table->json('placeholders'); // ['customer_name', 'app_name', ...]
            $table->timestamps();

            $table->index(['category', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
