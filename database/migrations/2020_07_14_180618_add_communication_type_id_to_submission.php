<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommunicationTypeIdToSubmission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Submission', function (Blueprint $table) {
            $table->unsignedInteger('communication_type_id') 
            ->nullable()
            ->default(null)
            ->after('type');

            $table->foreign('communication_type_id')
            ->references('communication_type_id')
            ->on('Communication_Type')
            ->onDelete('cascade');

            $table->dateTime('limit_date')->nullable()->default(null)->after('congress_id');
            $table->string('code')->nullable()->default(null)->after('limit_date');
            $table->dropColumn('prez_type');
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
            $table->dropColumn(['communication_type_id','limit_date','code']);
        });
    }
}
