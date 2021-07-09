<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStandProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('Stand_Product', function (Blueprint $table) {
        $table->increments("stand_product_id");
        $table->unsignedInteger('stand_id');
        $table->foreign("stand_id")
            ->references('stand_id')
            ->on('Stand')->onDelete('cascade');
        $table->string('name');
        $table->string('description')->nullable()->default(null);
        $table->string('main_img')->nullable()->default(null);
        $table->string('brochure_file')->nullable()->default(null);
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
      Schema::dropIfExists('Stand_Product');
    }
}
