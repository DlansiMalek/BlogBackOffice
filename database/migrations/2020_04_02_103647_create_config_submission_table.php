<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {                   
        
        Schema::create('Config_Submission', function (Blueprint $table) {
            $table->increments('config_sumbission_id');
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
             ->onDelete('cascade');
          
            $table->integer('max_words');
            $table->date('start_submission_date');
            $table->date('end_submission_date');
             
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
        Schema::dropIfExists('Config_Submission');
    }
}
