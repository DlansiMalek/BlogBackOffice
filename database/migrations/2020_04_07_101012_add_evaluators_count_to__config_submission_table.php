<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvaluatorsCountToConfigSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Submission', function (Blueprint $table) {
            $table->integer('num_evaluators')
            ->after('max_words');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_Submission', function (Blueprint $table) {
            $table->dropColumn('num_evaluators');       
        });
    }
}
