<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_id')->constrained('banks')->onDelete('cascade');
            $table->foreignId('card_type_id')->nullable()->constrained('card_types')->nullOnDelete();
            $table->string('card_last_four', 4)->nullable()->comment('Last 4 digits for display');
            $table->string('card_nickname')->nullable()->comment('User-defined name for the card');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'bank_id', 'card_type_id', 'card_last_four'], 'user_bank_card_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_banks');
    }
};
