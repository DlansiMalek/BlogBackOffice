<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBadgeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Badge', function (Blueprint $table) {
            $table->increments('badge_id');

            $table->string('img_name');

            $table->tinyInteger('qr_code_choice');
            $table->tinyInteger('text_choice');

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
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
        Schema::dropIfExists('Badge');
    }
}
