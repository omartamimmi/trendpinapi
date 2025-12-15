<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_throttle_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('geofence_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->string('notification_type')->default('geofence_entry');
            $table->string('event_type')->nullable(); // entry, exit, dwell
            $table->string('radar_event_id')->nullable();
            $table->decimal('user_lat', 10, 8)->nullable();
            $table->decimal('user_lng', 11, 8)->nullable();
            $table->enum('status', ['sent', 'throttled', 'failed', 'skipped'])->default('sent');
            $table->string('skip_reason')->nullable();
            $table->json('notification_data')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['user_id', 'sent_at']);
            $table->index(['user_id', 'brand_id', 'sent_at']);
            $table->index(['user_id', 'branch_id', 'sent_at']);
            $table->index(['user_id', 'offer_id', 'sent_at']);
            $table->index(['user_id', 'status', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_throttle_logs');
    }
};
