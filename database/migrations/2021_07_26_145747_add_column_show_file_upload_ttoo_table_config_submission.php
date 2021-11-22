<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnShowFileUploadTtooTableConfigSubmission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_Submission', function (Blueprint $table) {
            $table->tinyInteger('show_file_upload')->nullable()->default(1);
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
            $table->removeColumn('show_file_upload');
        });
    }
}
