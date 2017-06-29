<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuAddressCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lu_address_countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country')->unique();
            $table->integer('is_active')->length(1)->unsigned()->default(0);
        });
    }
}
