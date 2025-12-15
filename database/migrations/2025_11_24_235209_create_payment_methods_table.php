<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payment_methods')) {

            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 50)->unique(); // 'cliq_qr', 'jomopay', etc.
                $table->string('gateway', 50); // 'cliq', 'jomopay', 'stripe'
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable(); // API credentials, webhook URLs, etc.
                $table->integer('sort_order')->default(0);
                $table->text('description')->nullable();
                $table->string('icon_url')->nullable();
                $table->timestamps();

                $table->index('is_active');
                $table->index('sort_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
