<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Trendpin Blue", "Trendpin Pink"
            $table->string('color')->nullable(); // blue, pink
            $table->integer('offers_count'); // 35, 100
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // 0.00 for free, 250.00 JD
            $table->enum('billing_period', ['monthly', 'yearly'])->default('monthly');
            $table->integer('duration_months')->default(1);
            $table->integer('trial_days')->default(0); // 90 days = 3 months free
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
