<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsSubmissionToConfigLPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->tinyInteger('is_submission')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->removeColumn('is_submission');
        });
    }
}
