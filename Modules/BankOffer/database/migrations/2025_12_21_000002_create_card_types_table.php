<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->unsignedBigInteger('logo_id')->nullable();
            $table->enum('card_network', ['visa', 'mastercard', 'amex', 'other'])->default('other');
            $table->json('bin_prefixes')->nullable()->comment('Array of BIN prefixes (first 6 digits) for this card type');
            $table->string('card_color', 100)->nullable()->comment('Gradient or solid color for card display');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('bank_id')->references('id')->on('banks')->cascadeOnDelete();
            $table->foreign('logo_id')->references('id')->on('media_files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_types');
    }
};
