<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnSubmissionIdToTableUserMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('User_Mail', function (Blueprint $table) {
            $table->unsignedBigInteger('submission_id')->nullable()->default(null);
            $table->foreign('submission_id')->references('submission_id')->on('Submission')
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
        Schema::table('User_Mail', function (Blueprint $table) {
            $table->dropForeign('submission_id');
            $table->removeColumn('submission_id');
        });
    }
}
