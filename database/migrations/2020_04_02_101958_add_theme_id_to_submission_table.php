<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThemeIdAndCongressIdToSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Submission', function (Blueprint $table) {
            $table->unsignedInteger('theme_id');
            $table->foreign('theme_id')->references('theme_id')->on('Theme')
            ->onDelete('cascade');
            
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Submission', function (Blueprint $table) {
            
            $table->dropColumn('theme_id');
            $table->dropColumn('congress_id');
        });
    }
}
