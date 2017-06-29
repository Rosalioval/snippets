<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuAddressStatesTable extends Migration
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
            $table->integer('is_active')->length(1)->unsigned()->default(0);
        });

        Schema::table('lu_address_states', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('lu_address_countries');
        });
    }
}
