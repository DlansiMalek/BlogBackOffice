<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Access', function (Blueprint $table) {
            $table->increments('access_id');
            $table->double('price');
            $table->string('name');

            $table->integer('duration')->nullable()->default(null);
            $table->integer('seuil')->nullable()->default(null);
            $table->dateTime("start_date")->nullable()->default(null);

            $table->dateTime("theoric_start_data")->nullable()->default(null);
            $table->dateTime("theoric_end_data")->nullable()->default(null);

            $table->tinyInteger("block")->default(0);
            $table->tinyInteger("intuitive")->nullable()->default(null);

            $table->integer("max_places")->nullable()->default(null);
            $table->integer("total_present_in_congress")->default(0);

            $table->boolean('packless')->default(false);

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
        Schema::dropIfExists('Access');
    }
}
