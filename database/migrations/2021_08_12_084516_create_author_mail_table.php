<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorMailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Author_Mail', function (Blueprint $table) {
            $table->increments('author_mail_id');

            $table->tinyInteger('status')->default(0);

            $table->unsignedInteger('author_id');
            $table->foreign('author_id')->references('author_id')->on('Author')->onDelete('cascade');

            $table->unsignedInteger('mail_id');
            $table->foreign('mail_id')->references('mail_id')->on('Mail')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Author_Mail', function (Blueprint $table) {
            $table->dropForeign(['mail_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('Author_Mail');
    }
}
