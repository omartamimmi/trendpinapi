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
        // Skip if columns already exist
        if (Schema::hasColumn('brands', 'title')) {
            return;
        }

        Schema::table('brands', function (Blueprint $table) {
            // Core shop fields
            $table->string('title')->after('name')->nullable();
            $table->string('slug')->after('title')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('title_ar')->nullable();

            // Media fields
            $table->integer('image_id')->nullable();
            $table->string('video')->nullable();
            $table->string('featured_mobile')->nullable();

            // Status and publishing
            $table->string('status')->default('draft');
            $table->dateTime('publish_date')->nullable();
            $table->string('days')->nullable();
            $table->tinyInteger('open_status')->default(0)->nullable();
            $table->bigInteger('featured')->default(0)->nullable();

            // Location fields
            $table->bigInteger('location_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();

            // Branch fields (for compatibility with pinpoint)
            $table->integer('is_main_branch')->default(1);
            $table->integer('main_branch_id')->nullable();

            // Business type
            $table->string('type')->nullable();

            // External IDs
            $table->bigInteger('source_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'slug', 'description', 'description_ar', 'title_ar',
                'image_id', 'video', 'featured_mobile',
                'status', 'publish_date', 'days', 'open_status', 'featured',
                'location_id', 'phone_number', 'lat', 'lng',
                'is_main_branch', 'main_branch_id', 'type', 'source_id'
            ]);
        });
    }
};
