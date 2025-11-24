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
        Schema::create('staged_forms', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('stage_id');
            $table->string('stage_type');
            // $table->string('step');
            // $table->json('submitted_form');
            $table->timestamps();
            $table->unique(['stage_id', 'stage_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staged_forms');
    }
};
