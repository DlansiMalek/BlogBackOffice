<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUserNetwork extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Network', function (Blueprint $table) {
            $table->increments("user_network_id");

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')
                ->on('User')->onDelete('cascade');

            $table->unsignedInteger('fav_id');
            $table->foreign('fav_id')->references('user_id')
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
        Schema::table('User_Network', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['fav_id']);
        });
        Schema::dropIfExists('User_Network');
    }
}
