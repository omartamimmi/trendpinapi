<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exact_locations', function (Blueprint $table) {
            $table->double('lat')->nullable()->change();
            $table->double('lng')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('exact_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exact_locations', function (Blueprint $table) {

        });
    }
};
