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
            $table->softDeletes();

            $table->increments('access_id');
            $table->double('price')->default(0);
            $table->string('name');

            //description
            $table->string('description');

            //room
            $table->string('room');

            $table->integer('parent_id')->unsigned();
            $table->foreign('parent_id')->references('access_id')->on('Access');


            $table->integer('duration')->nullable()->default(null);
            $table->integer('seuil')->nullable()->default(null);
            $table->dateTime("start_date")->nullable()->default(null);
            $table->dateTime("end_date")->nullable()->default(null);

            $table->dateTime("real_start_date")->nullable()->default(null);


            $table->integer("max_places")->nullable()->default(null);
            $table->integer("total_present_in_congress")->default(0);

            $table->boolean('packless')->default(false);

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
                ->onDelete('cascade');

            //topic
            $table->integer('topic_id')->unsigned();
            $table->foreign('topic_id')->references('topic_id')->on('Topic');


            //access_type
            $table->integer('access_type_id')->unsigned();
            $table->foreign('access_type_id')->references('access_type_id')->on('Access_Type');


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
