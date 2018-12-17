<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCustomMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Custom_Mail', function (Blueprint $table) {
            $table->increments("custom_mail_id");
            $table->unsignedInteger("congress_id");
            $table->string('object');
            $table->text("template");
            $table->foreign("congress_id")->references("congress_id")->on('congress');
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
