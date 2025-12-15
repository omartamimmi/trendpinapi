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
        Schema::create('mediables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_file_id');
            $table->morphs('mediable'); // mediable_type, mediable_id
            $table->string('collection')->default('default'); // e.g., 'logo', 'gallery', 'license'
            $table->integer('order')->default(0);
            $table->json('custom_properties')->nullable();
            $table->timestamps();

            $table->foreign('media_file_id')
                ->references('id')
                ->on('media_files')
                ->onDelete('cascade');

            $table->index(['mediable_type', 'mediable_id', 'collection']);
            $table->unique(['media_file_id', 'mediable_type', 'mediable_id', 'collection'], 'mediables_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mediables');
    }
};
