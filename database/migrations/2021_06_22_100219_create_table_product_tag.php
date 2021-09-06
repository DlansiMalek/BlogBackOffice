<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductTag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Product_Tag', function (Blueprint $table) {
            $table->increments("product_tag_id");

            $table->unsignedInteger("tag_id");
            $table->foreign("tag_id")->references('tag_id')->on('Tag')
                ->onDelete('cascade');

            $table->unsignedInteger("stand_product_id");
            $table->foreign("stand_product_id")->references('stand_product_id')->on('Stand_Product')
                ->onDelete('cascade');

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
        Schema::table('Product_Tag', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->dropForeign(['stand_product_id']);
        });
        Schema::dropIfExists('Product_Tag');
    }
}
