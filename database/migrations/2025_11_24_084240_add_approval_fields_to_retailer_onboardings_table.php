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
        Schema::table('retailer_onboardings', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'pending_approval', 'approved', 'changes_requested', 'rejected'])
                ->default('pending')
                ->after('status');
            $table->text('admin_notes')->nullable()->after('approval_status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('admin_notes');
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailer_onboardings', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'admin_notes', 'approved_by', 'approved_at']);
        });
    }
};
