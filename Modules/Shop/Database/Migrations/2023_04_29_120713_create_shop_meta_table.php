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
        Schema::create('shop_meta', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shop_id');
            $table->tinyInteger('enable_open_hours')->nullable();
            $table->text('open_hours')->nullable();
            $table->tinyInteger('enable_discount')->nullable();
            $table->string('discount_type')->nullable();
            $table->text('discount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_meta');
    }
};
