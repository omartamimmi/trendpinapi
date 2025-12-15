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
        if (Schema::hasTable('offers')) {
            return;
        }

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'bogo'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('max_claims')->nullable();
            $table->integer('claims_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->text('terms')->nullable();
            $table->json('branch_ids')->nullable(); // Store selected branch IDs
            $table->boolean('all_branches')->default(false);
            $table->enum('status', ['draft', 'active', 'paused', 'expired'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
