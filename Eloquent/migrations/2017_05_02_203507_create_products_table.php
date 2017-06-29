<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->string('description', 3500);
            $table->decimal('cost', 13, 2)->unsigned()->nullable();
            $table->decimal('price', 13, 2)->unsigned()->nullable();
            $table->decimal('discount', 2, 2)->unsigned()->nullable();
            $table->integer('brand_id')->unsigned();
            $table->timestamps();
        });
    }
}
