<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('radar_user_id')->nullable()->index();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->decimal('accuracy', 10, 2)->nullable(); // meters
            $table->string('fcm_token')->nullable();
            $table->string('device_id')->nullable();
            $table->string('device_type')->nullable(); // ios, android
            $table->json('metadata')->nullable();
            $table->boolean('is_tracking_enabled')->default(true);
            $table->timestamp('location_updated_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['lat', 'lng']);
            $table->index('fcm_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_locations');
    }
};
