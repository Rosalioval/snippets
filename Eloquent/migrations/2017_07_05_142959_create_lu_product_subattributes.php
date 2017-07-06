<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuProductSubattributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lu_product_subattributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('attribute_id')->unsigned();
            $table->string('subattribute');
            $table->timestamps();
        });

        Schema::table('lu_product_subattributes', function (Blueprint $table) {
            $table->foreign('attribute_id')->references('id')->on('lu_product_attributes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lu_product_subattributes', function (Blueprint $table) {
            $table->dropForeign(['attribute_id']);
        });

        Schema::dropIfExists('lu_product_subattributes');
    }
}
