<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuAddressStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lu_address_states', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned();
            $table->string('state');
        });

        Schema::table('lu_address_states', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('lu_address_countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lu_address_states', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });

        Schema::dropIfExists('lu_address_states');
    }
}
