<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStandProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stand_product', function (Blueprint $table) {
            $table->increments("stand_product_id");
			$table->unsignedInteger('stand_id');
            $table->foreign("stand_id")
                ->references('stand_id')
                ->on('stand');
		    $table->string('name')->nullable()->default(null);
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
        Schema::dropIfExists('stand_product');
    }
}
