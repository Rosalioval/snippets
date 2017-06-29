<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuProductTaxSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lu_product_tax_schema', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('tax', 2, 2)->unsigned();
            $table->integer('country_id')->unsigned()->nullable();
        });
    }
}
