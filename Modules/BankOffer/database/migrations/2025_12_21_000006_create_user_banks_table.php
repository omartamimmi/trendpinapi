<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old table if exists
        Schema::dropIfExists('user_banks');

        // Create simple pivot table
        Schema::create('user_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_id')->constrained('banks')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'bank_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_banks');
    }
};
