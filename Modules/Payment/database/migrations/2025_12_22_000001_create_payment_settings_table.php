<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();

            // Gateway identification
            $table->string('gateway', 50)->unique(); // tap, hyperpay, paytabs, cliq
            $table->string('display_name', 100);
            $table->string('display_name_ar', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();

            // Status
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_sandbox')->default(true);

            // Credentials (encrypted)
            $table->text('credentials')->nullable(); // JSON encrypted

            // Supported payment methods
            $table->json('supported_methods')->nullable(); // ['card', 'apple_pay', 'google_pay', 'cliq']

            // Display settings
            $table->string('icon')->nullable();
            $table->string('logo_id')->nullable();
            $table->integer('sort_order')->default(0);

            // Fee settings
            $table->decimal('fee_percentage', 5, 2)->default(0);
            $table->decimal('fee_fixed', 10, 2)->default(0);

            // Limits
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();

            // Webhook settings
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();

            // Admin tracking
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Payment method settings (granular control)
        Schema::create('payment_method_settings', function (Blueprint $table) {
            $table->id();
            $table->string('method', 50); // card, apple_pay, google_pay, cliq
            $table->string('display_name', 100);
            $table->string('display_name_ar', 100)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('preferred_gateway', 50)->nullable(); // Which gateway to use for this method
            $table->integer('sort_order')->default(0);
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->unique('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_settings');
        Schema::dropIfExists('payment_settings');
    }
};
