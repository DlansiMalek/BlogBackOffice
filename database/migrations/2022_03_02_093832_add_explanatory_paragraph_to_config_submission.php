<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExplanatoryParagraphToConfigSubmission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Submission', function (Blueprint $table) {
         $table->text('explanatory_paragraph')->nullable()->default(null);
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Config_Submission', function (Blueprint $table) {
         $table->removeColumn('explanatory_paragraph');
           
        });
    }
}
