<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_cart', function (Blueprint $table) {
            $table->increments('id');
            $table->string('session')->comment('A user laravel_session cookie. If the cookie changes, the cart will not longer be available.');
            $table->integer('product_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('status_id')->unsigned()->comment('Determines a user shopping cart status. It can be in_session if the user is still shopping no matter if it is logged in or not; in_order if the user already ordered the products.');
            $table->timestamps();
        });
    }
}
