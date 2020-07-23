<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommunicationTypeToSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Submission', function (Blueprint $table) {
            $table->dropColumn('prez_type');
            $table->integer('communication_type_id')->unsigned()->nullable()->default(null);
            $table->foreign('communication_type_id')->references('communication_type_id')->on('Communication_Type')->onDelete('set null');;
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
            //
        });
    }
}
