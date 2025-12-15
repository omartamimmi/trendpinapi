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
        // Skip this migration if it's already been applied or column doesn't exist
        if (!Schema::hasColumn('brands', 'business_id')) {
            return;
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable(false)->change();
        });
    }
};
