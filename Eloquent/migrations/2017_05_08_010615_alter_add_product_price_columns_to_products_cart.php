<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddProductPriceColumnsToProductsCart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_cart', function (Blueprint $table) {
            $table->decimal('cost', 13, 2)->unsigned()->nullable()->after('status_id');
            $table->decimal('price', 13, 2)->unsigned()->nullable()->after('cost');
            $table->decimal('discount', 2, 2)->unsigned()->nullable()->after('price');
        });
    }
}
