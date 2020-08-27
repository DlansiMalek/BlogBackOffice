<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Item_Note', function (Blueprint $table) {
            $table->increments('item_note_id');
            $table->unsignedInteger('item_evaluation_id');
            $table->foreign('item_evaluation_id')->references('item_evaluation_id')
            ->on('Item_Evaluation');
            $table->unsignedBigInteger('evaluation_inscription_id');
            $table->foreign('evaluation_inscription_id')->references('evaluation_inscription_id')
            ->on('Evaluation_Inscription');
            $table->integer('note')->nullable()->default(-1);
            $table->string('comment')->nullable()->default(null);
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
        Schema::dropIfExists('Item_Note');
    }
}
