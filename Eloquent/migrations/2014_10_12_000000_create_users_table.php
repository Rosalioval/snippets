<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('paternal_last_name')->nullable();
            $table->string('maternal_last_name')->nullable();
            $table->string('mobile_number', 20)->nullable()->unique();
            $table->integer('role_id')->length(2)->unsigned()->comment('FK references lu_user_roles table. The role assigned to the user.');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
