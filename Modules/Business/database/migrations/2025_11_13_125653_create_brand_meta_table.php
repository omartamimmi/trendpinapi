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
        if (Schema::hasTable('brand_meta')) {
            return;
        }

        Schema::create('brand_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->tinyInteger('enable_open_hours')->nullable();
            $table->text('open_hours')->nullable();
            $table->tinyInteger('enable_discount')->nullable();
            $table->string('discount_type')->nullable();
            $table->text('discount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_meta');
    }
};
