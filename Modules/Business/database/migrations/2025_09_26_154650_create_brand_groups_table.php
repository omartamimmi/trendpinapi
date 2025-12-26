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
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained()->cascadeOnDelete(); // The retailer
                // $table->foreignId('brand_id')->constrained()->cascadeOnDelete(); // The retailer
                // $table->foreignId('brand_id')->nullable()->constrained('brands')->cascadeOnDelete();
                $table->string('name');             // Group name
                $table->string('logo')->nullable(); // Group logo
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
