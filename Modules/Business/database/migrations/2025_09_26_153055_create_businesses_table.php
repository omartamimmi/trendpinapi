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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('retailer_name')->nullable();  // Retailer Name
            $table->string('retailer_email')->unique();
            $table->string('phone_number')->nullable();
            $table->enum('business_type', ['single', 'group'])->default('single'); // business structure
            $table->string('license_file')->nullable();
            $table->string('status')->default('draft');  
            $table->enum('operation_type', ['in_person', 'online', 'hybrid'])->default('in_person'); // service type  
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->softDeletes();
            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
