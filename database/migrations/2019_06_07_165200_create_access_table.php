<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->string('name');
            $table->double('price')->default(0);
            $table->integer('duration')->nullable()->default(null);
            $table->integer("max_places")->nullable()->default(null);
            $table->integer("total_present_in_congress")->default(0);
            $table->integer('seuil')->nullable()->default(null);
            $table->string('room')->nullable()->default(null);
            $table->string('description')->nullable()->default(null);
            $table->dateTime("real_start_date")->nullable()->default(null);
            $table->dateTime("start_date");
            $table->dateTime("end_date");
            $table->unsignedTinyInteger('packless')->default(1);
            $table->unsignedTinyInteger('show_in_program')->nullable()->default(null);

            $table->integer('parent_id')->unsigned()->nullable()->default(null);
            $table->foreign('parent_id')->references('access_id')->on('Access')->onDelete('cascade');

            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
                ->onDelete('cascade');

            //topic
            $table->integer('topic_id')->unsigned()->nullable()->default(null);
            $table->foreign('topic_id')->references('topic_id')->on('Topic');


            //access_type
            $table->integer('access_type_id')->unsigned()->nullable()->default(null);
            $table->foreign('access_type_id')->references('access_type_id')->on('Access_Type');

            $table->softDeletes();
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
        Schema::dropIfExists('access');
    }
}
