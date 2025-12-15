<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('retailer_payment_methods')) {
            return;
        }

        Schema::create('retailer_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['cliq', 'bank']);
            // Cliq fields
            $table->string('cliq_number', 30)->nullable();
            $table->boolean('cliq_verified')->default(false);
            // Bank fields
            $table->string('bank_name')->nullable();
            $table->string('iban', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retailer_payment_methods');
    }
};
