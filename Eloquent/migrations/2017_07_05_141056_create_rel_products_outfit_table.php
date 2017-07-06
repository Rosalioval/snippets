<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelProductsOutfitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rel_products_outfit', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('outfit_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('rel_products_outfit', function (Blueprint $table) {
            $table->foreign('outfit_id')->references('id')->on('outfits');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rel_products_outfit', function (Blueprint $table) {
            $table->dropForeign(['outfit_id']);
        });
        Schema::table('rel_products_outfit', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('rel_products_outfit', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('rel_products_outfit');
    }
}
