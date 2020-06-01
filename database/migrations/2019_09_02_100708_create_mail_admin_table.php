<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Mail_Admin', function (Blueprint $table) {
            $table->increments("mail_id");


            $table->string('object');
            $table->text("template");


            $table->unsignedInteger("mail_type_id");
            $table->foreign("mail_type_id")->references('mail_type_id')->on('Mail_Type_Admin');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Mail_Admin', function (Blueprint $table) {
            $table->dropForeign(['mail_type_id']);
        });
        Schema::dropIfExists('Mail_Admin');
    }
}
