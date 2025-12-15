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
        if (Schema::hasColumn('subscription_plans', 'type')) {
            return;
        }

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->enum('type', ['user', 'retailer', 'bank'])->default('retailer')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
