<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelProductsCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rel_products_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('subcategory_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('rel_products_categories', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('category_id')->references('id')->on('lu_product_categories');
            $table->foreign('subcategory_id')->references('id')->on('lu_product_subcategories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rel_products_categories', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        Schema::table('rel_products_categories', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
        });
        Schema::table('rel_products_categories', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::dropIfExists('rel_products_categories');
    }
}
