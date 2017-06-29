<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FkUsersAddressToLuAddressCountriesAndStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_address', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('lu_address_countries');
            $table->foreign('state_id')->references('id')->on('lu_address_states');
        });
    }
}
