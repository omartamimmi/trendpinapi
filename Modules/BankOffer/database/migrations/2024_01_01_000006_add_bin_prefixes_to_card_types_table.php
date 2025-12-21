<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_types', function (Blueprint $table) {
            $table->json('bin_prefixes')->nullable()->after('card_network')
                ->comment('Array of BIN prefixes (first 6 digits) for this card type');
        });
    }

    public function down(): void
    {
        Schema::table('card_types', function (Blueprint $table) {
            $table->dropColumn('bin_prefixes');
        });
    }
};
