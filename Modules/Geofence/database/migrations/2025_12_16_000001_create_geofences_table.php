<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->string('radar_geofence_id')->nullable()->unique();
            $table->string('external_id')->nullable()->index();
            $table->string('tag')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->unsignedInteger('radius')->default(100); // meters
            $table->enum('type', ['circle', 'polygon'])->default('circle');
            $table->json('coordinates')->nullable(); // For polygon geofences
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('synced_to_radar')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lat', 'lng']);
            $table->index(['is_active', 'synced_to_radar']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
