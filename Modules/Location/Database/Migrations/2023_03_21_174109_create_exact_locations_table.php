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
        Schema::create('exact_locations', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->string('exact_address');
            $table->integer('shop_id');
            $table->double('lat');
            $table->double('lng');
            $table->integer('image_id')->nullable();
            $table->string('slug')->unique();
            $table->string('city')->nullable();
            $table->string('country')->nullable();;
            $table->string('state')->nullable();;
            $table->string('zip')->nullable();;
            $table->string('country_code')->nullable();;
            $table->bigInteger('create_user')->nullable();
            $table->bigInteger('update_user')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('exact_locations');
    }
};
