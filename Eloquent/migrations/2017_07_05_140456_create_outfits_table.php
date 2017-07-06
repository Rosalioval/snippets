<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutfitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outfits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('excerpt', 500);
            $table->string('description', 3500);
            $table->decimal('cost', 13, 2)->unsigned()->nullable();
            $table->decimal('price', 13, 2)->unsigned()->nullable();
            $table->decimal('discount', 2, 2)->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outfits');
    }
}
