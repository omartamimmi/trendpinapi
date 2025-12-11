<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('qr_payments')) {

            Schema::table('qr_payments', function (Blueprint $table) {
                // Add branch_id column first
                $table->foreignId('branch_id')->nullable()->after('merchant_id')->constrained('branches')->cascadeOnDelete();

                // Add user_id to track which user generated the QR (optional, for audit)
                $table->foreignId('user_id')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('qr_payments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['branch_id', 'user_id']);
        });
    }
};
