<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNotifCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Notif_Congress', function (Blueprint $table) {
            $table->bigIncrements('user_notif_congress_id');

            $table->string("firebase_key_user");

            $table->unsignedInteger('user_id')
                ->nullable()
                ->default(null);

            $table->foreign('user_id')
                ->references('user_id')
                ->on('User')
                ->onDelete('set null');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')
                ->references('congress_id')
                ->on('Congress')
                ->onDelete('cascade');

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
        Schema::table('User_Notif_Congress', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('User_Notif_Congress');
    }
}
