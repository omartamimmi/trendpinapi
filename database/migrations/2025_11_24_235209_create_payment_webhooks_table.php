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
        if (!Schema::hasTable('payment_webhooks')) {

            Schema::create('payment_webhooks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                $table->string('gateway', 50);
                $table->string('event_type', 100);
                $table->json('payload');
                $table->boolean('processed')->default(false);
                $table->timestamp('processed_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['gateway', 'event_type']);
                $table->index('processed');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
    }
};
