<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Response', function (Blueprint $table) {
            $table->increments('response_id');
            $table->text('response');
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')
                    ->on('User')->onDelete('cascade');
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
        Schema::table('Response', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('Response');
    }
}
