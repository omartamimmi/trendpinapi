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
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('businesses')->onDelete('cascade');
                // $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');
                $table->string('name');
                $table->string('logo')->nullable();
                $table->string('location')->nullable();
                $table->timestamps();
                $table->text('gallery')->nullable();
                // moved here
                $table->string('website_link')->nullable();
                $table->string('insta_link')->nullable();
                $table->string('facebook_link')->nullable();
                $table->bigInteger('create_user')->nullable();
                $table->bigInteger('update_user')->nullable();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
