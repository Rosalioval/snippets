<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('The client to be delivered');
            $table->string('products_cart_session')->unique()->comment('A group of products to be delivered');
            $table->integer('status_id')->unsigned()->default(1)->comment('Defines the status of the order: pending | paid | in_transit | delivered | returned');
            $table->decimal('subtotal', 13, 2)->unsigned()->nullable();
            $table->integer('tax_id')->unsigned()->nullable();
            $table->decimal('discount', 2, 2)->unsigned()->nullable();
            $table->decimal('total', 13, 2)->unsigned()->nullable();
            $table->dateTime('deliver_at')->nullable();
            $table->timestamps();
        });
    }
}
