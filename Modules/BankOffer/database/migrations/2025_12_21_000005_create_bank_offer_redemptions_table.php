<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_offer_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_offer_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('discount_applied', 10, 2)->nullable();

            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->foreign('bank_offer_id')->references('id')->on('bank_offers')->cascadeOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['bank_offer_id', 'redeemed_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_offer_redemptions');
    }
};
