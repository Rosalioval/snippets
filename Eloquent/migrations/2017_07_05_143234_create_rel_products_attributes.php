<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelProductsAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rel_products_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('attribute_id')->unsigned();
            $table->integer('subattribute_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('rel_products_attributes', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('attribute_id')->references('id')->on('lu_product_attributes');
            $table->foreign('subattribute_id')->references('id')->on('lu_product_subattributes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rel_products_attributes', function (Blueprint $table) {
            $table->dropForeign(['attribute_id']);
        });
        Schema::table('rel_products_attributes', function (Blueprint $table) {
            $table->dropForeign(['subattribute_id']);
        });
        Schema::table('rel_products_attributes', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::dropIfExists('rel_products_attributes');
    }
}
