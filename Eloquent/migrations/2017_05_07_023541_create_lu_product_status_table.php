<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuProductStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lu_product_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status')->unique();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('status_id')->unsigned()->default(1)->after('user_id');
            $table->foreign('status_id')->references('id')->on('lu_product_status');
        });
    }
}
