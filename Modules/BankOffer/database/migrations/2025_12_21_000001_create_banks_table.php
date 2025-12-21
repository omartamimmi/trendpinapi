<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->unsignedBigInteger('logo_id')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('logo_id')->references('id')->on('media_files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
