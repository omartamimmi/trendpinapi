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
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable()->after('location');
            }
            if (!Schema::hasColumn('branches', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable()->after('lat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
