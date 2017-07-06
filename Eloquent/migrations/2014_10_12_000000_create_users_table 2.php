<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\LuUserRole;

class CreateUsersTable extends Migration
{
    const CLIENT_ROLE_ID = 3;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->length(2)->unsigned()->default(self::CLIENT_ROLE_ID)->comment('FK references lu_user_roles table. The role assigned to the user.');
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('paternal_last_name')->nullable();
            $table->string('maternal_last_name')->nullable();
            $table->string('mobile_number', 20)->nullable()->unique();
            $table->integer('gender_id')->length(1)->unsigned()->nullable();
            $table->date('dob')->nullable();
            $table->string('token', 30)->nullable();
            $table->integer('is_verified')->length(1)->default(0);
            $table->string('referral_code', 7)->nullable();
            $table->integer('referred_by')->unsigned()->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
