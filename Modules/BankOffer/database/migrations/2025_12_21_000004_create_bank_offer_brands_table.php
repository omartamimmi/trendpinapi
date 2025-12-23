<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_offer_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_offer_id');
            $table->unsignedBigInteger('brand_id');

            $table->boolean('all_branches')->default(true);
            $table->json('branch_ids')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamps();

            $table->foreign('bank_offer_id')->references('id')->on('bank_offers')->cascadeOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['bank_offer_id', 'brand_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_offer_brands');
    }
};
