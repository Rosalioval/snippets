<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FkProductsCartToLuProductsCartStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_cart', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('lu_products_cart_status');
        });
    }
}
