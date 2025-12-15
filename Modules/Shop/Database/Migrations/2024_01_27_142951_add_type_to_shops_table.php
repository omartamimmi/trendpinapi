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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('type')->nullable()->default('in_person');
            $table->string('website_link')->nullable();
            $table->string('insta_link')->nullable();
            $table->string('facebook_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('website_link');
            $table->dropColumn('insta_link');
            $table->dropColumn('facebook_link');
        });
    }
};
