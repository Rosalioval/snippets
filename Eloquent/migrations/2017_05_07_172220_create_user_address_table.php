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
            $table->integer('country_id')->unsigned();
            $table->integer('state_id')->unsigned();
            $table->string('city');
            $table->string('street');
            $table->string('interior')->nullable();
            $table->string('neighborhood');
            $table->timestamps();
        });
        Schema::table('user_address', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
