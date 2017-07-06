<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelOutfitsGallery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rel_outfits_gallery', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('outfit_id')->unsigned();
            $table->string('img_src');
            $table->string('s3_version_hash');
            $table->integer('is_featured')->length(1)->unsigned()->nullable()->default(0);
            $table->timestamps();
        });

        Schema::table('rel_outfits_gallery', function (Blueprint $table) {
            $table->foreign('outfit_id')->references('id')->on('outfits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rel_outfits_gallery', function (Blueprint $table) {
            $table->dropForeign(['outfit_id']);
        });

        Schema::dropIfExists('rel_outfits_gallery');
    }
}
