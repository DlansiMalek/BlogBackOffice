<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableResourceProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Resource_Product', function (Blueprint $table) {
            $table->increments("resource_product_id");

            $table->unsignedInteger("stand_product_id");
            $table->foreign("stand_product_id")->references('stand_product_id')->on('stand_product')
                ->onDelete('cascade');

            $table->unsignedInteger("resource_id");
            $table->foreign("resource_id")->references('resource_id')->on('Resource')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Resource_Product', function (Blueprint $table) {
            $table->dropForeign(['stand_product_id']);
            $table->dropForeign(['resource_id']);
        });
        Schema::dropIfExists('Resource_Product');
    }
}
