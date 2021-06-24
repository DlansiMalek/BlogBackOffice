<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductVideo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Product_Video', function (Blueprint $table) {
            $table->increments("product_video_id");

            $table->unsignedInteger("stand_product_id");
            $table->foreign("stand_product_id")->references('stand_product_id')->on('Stand_Product')
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
        Schema::table('Product_Video', function (Blueprint $table) {
            $table->dropForeign(['stand_product_id']);
            $table->dropForeign(['resource_id']);
          });
        Schema::dropIfExists('Product_Video');
    }
}
