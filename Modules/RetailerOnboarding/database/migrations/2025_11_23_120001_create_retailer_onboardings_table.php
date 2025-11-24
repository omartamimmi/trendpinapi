<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retailer_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('current_step', [
                'retailer_details',
                'payment_details',
                'brand_information',
                'subscription',
                'payment',
                'completed'
            ])->default('retailer_details');
            $table->boolean('phone_verified')->default(false);
            $table->boolean('cliq_verified')->default(false);
            $table->json('completed_steps')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retailer_onboardings');
    }
};
