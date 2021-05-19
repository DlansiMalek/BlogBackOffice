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
        Schema::create('resource_product', function (Blueprint $table) {
           $table->increments("resource_product_id");
			
		   $table->string("file_name")
                ->nullable()->default(null);
            
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
        Schema::dropIfExists('resource_product');
    }
}
