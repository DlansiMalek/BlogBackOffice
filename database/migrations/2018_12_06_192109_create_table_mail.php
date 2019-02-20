<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMail extends Migration
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


            $table->unsignedInteger("congress_id");
            $table->foreign("congress_id")->references("congress_id")->on('Congress');

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
        //
    }
}
