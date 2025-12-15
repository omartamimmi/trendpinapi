<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index(); // debug, info, notice, warning, error, critical, alert, emergency
            $table->string('channel', 50)->index(); // application, auth, api, queue, etc.
            $table->text('message');
            $table->json('context')->nullable(); // Additional context data
            $table->json('extra')->nullable(); // Extra data from processors

            // User tracking
            $table->foreignId('user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('user_type', 50)->nullable()->index(); // admin, retailer, customer

            // Request tracking
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('request_url', 500)->nullable();
            $table->string('request_id', 36)->nullable()->index(); // UUID for request correlation

            // Performance tracking
            $table->float('duration_ms')->nullable(); // Request duration in milliseconds
            $table->integer('memory_usage')->nullable(); // Memory usage in bytes

            // Exception tracking
            $table->string('exception_class')->nullable()->index();
            $table->text('exception_message')->nullable();
            $table->text('exception_trace')->nullable();
            $table->string('exception_file')->nullable();
            $table->integer('exception_line')->nullable();

            $table->timestamp('logged_at')->index();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['level', 'logged_at']);
            $table->index(['channel', 'logged_at']);
            $table->index(['user_id', 'logged_at']);
            $table->index(['logged_at', 'level', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
