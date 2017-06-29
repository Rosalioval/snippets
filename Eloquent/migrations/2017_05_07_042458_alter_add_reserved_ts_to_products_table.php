<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddReservedTsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->datetime('reserved_ts')->nullable()->after('user_id')->comment('Adds a limited TS to the product after the user adds it to the cart so she can buy it before in case stock finishes.');
        });
    }
}
