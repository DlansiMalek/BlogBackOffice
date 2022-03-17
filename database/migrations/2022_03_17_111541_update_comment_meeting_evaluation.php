<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCommentMeetingEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Meeting_Evaluation', function (Blueprint $table) {
            $table->text('comment')->default(Null)->nullable()->change(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Meeting_Evaluation', function (Blueprint $table) {
            $table->string('comment')->default(Null)->nullable()->change(); 
        });
    }
}
