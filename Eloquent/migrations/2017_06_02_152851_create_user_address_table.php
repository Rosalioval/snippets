<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_address', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('country_id')->unsigned()->nullable()->default(null);
            $table->integer('state_id')->unsigned()->nullable()->default(null);
            $table->string('zip_code')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('street')->nullable()->default(null);
            $table->string('interior')->nullable()->default(null);
            $table->string('neighborhood')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('user_address', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('country_id')->references('id')->on('lu_address_countries');
            $table->foreign('state_id')->references('id')->on('lu_address_states');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_address', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
        });

        Schema::dropIfExists('user_address');
    }
}
