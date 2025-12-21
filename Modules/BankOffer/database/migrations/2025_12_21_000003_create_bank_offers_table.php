<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('card_type_id')->nullable();

            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();

            $table->enum('offer_type', ['percentage', 'fixed', 'cashback']);
            $table->decimal('offer_value', 10, 2);
            $table->decimal('min_purchase_amount', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();

            $table->date('start_date');
            $table->date('end_date');

            $table->text('terms')->nullable();
            $table->text('terms_ar')->nullable();

            $table->enum('redemption_type', ['show_only', 'qr_code', 'in_app'])->default('show_only');

            $table->enum('status', ['draft', 'pending', 'active', 'paused', 'expired'])->default('draft');
            $table->unsignedInteger('total_claims')->default(0);
            $table->unsignedInteger('max_claims')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bank_id')->references('id')->on('banks')->cascadeOnDelete();
            $table->foreign('card_type_id')->references('id')->on('card_types')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['status', 'start_date', 'end_date']);
            $table->index('bank_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_offers');
    }
};
