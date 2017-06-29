<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddsProductAmountToProductsCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_cart', function (Blueprint $table) {
            $table->integer('amount')->length(6)->unsigned()->default(0)->after('product_id');
        });
    }
}
