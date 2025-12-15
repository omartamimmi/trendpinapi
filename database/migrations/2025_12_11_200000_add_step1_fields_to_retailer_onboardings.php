<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retailer_onboardings', function (Blueprint $table) {
            $table->string('city')->nullable()->after('user_id');
            $table->string('category')->nullable()->after('city');
            $table->string('logo_path')->nullable()->after('category');
            $table->string('license_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('retailer_onboardings', function (Blueprint $table) {
            $table->dropColumn(['city', 'category', 'logo_path', 'license_path']);
        });
    }
};
