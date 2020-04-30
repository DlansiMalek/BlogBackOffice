<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Mail', function (Blueprint $table) {
            $table->increments("mail_id");


            $table->string('object');
            $table->text("template");


            $table->unsignedInteger("congress_id")->nullable()->default(null);
            $table->foreign("congress_id")->references("congress_id")->on('Congress')->onDelete('cascade');

            $table->unsignedInteger("mail_type_id");
            $table->foreign("mail_type_id")->references('mail_type_id')->on('Mail_Type');

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
        Schema::table('Mail', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['mail_type_id']);
        });
        Schema::dropIfExists('Mail');
    }
}
