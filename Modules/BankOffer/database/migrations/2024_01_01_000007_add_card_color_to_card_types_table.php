<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_types', function (Blueprint $table) {
            $table->string('card_color', 100)->nullable()->after('bin_prefixes')
                ->comment('Gradient or solid color for card display');
        });
    }

    public function down(): void
    {
        Schema::table('card_types', function (Blueprint $table) {
            $table->dropColumn('card_color');
        });
    }
};
