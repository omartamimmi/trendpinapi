<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tag'); // nearby, new_offer, etc
            $table->string('title_template'); // "{{count}} new offers near you!"
            $table->text('body_template'); // "Check out {{brand_name}} and more"
            $table->string('action_type')->nullable(); // brands, offers, brand_detail
            $table->json('action_data')->nullable();
            $table->string('image_url')->nullable();
            $table->string('deep_link_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('tag');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
