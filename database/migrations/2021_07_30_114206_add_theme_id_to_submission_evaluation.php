<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThemeIdToSubmissionEvaluation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Submission_Evaluation', function (Blueprint $table) {
            $table->unsignedInteger('theme_id')->nullable()->default(null);
            $table->foreign('theme_id')->references('theme_id')->on('Theme')
            ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Submission_Evaluation', function (Blueprint $table) {
            $table->dropForeign(['theme_id']);
            $table->removeColumn('theme_id');
        });
    }
}
