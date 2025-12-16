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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->enum('type', ['mall', 'shopping_district', 'plaza', 'market', 'other'])->default('mall');
            $table->text('address')->nullable();
            $table->text('address_ar')->nullable();
            $table->string('city')->nullable();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->integer('radius')->default(200); // Default 200 meters
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();

            $table->index(['lat', 'lng']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
