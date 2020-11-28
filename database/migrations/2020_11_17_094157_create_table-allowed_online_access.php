<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAllowedOnlineAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Allowed_Online_Access', function (Blueprint $table) {
            $table->increments('allowed_online_access_id');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')
                ->onDelete('cascade');

            $table->unsignedInteger('privilege_id');
            $table->foreign('privilege_id')->references('privilege_id')
                ->on('Privilege')
                ->onDelete('cascade');

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
        Schema::table('Allowed_Online_Access', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['privilege_id']);
        });
        Schema::dropIfExists('Allowed_Online_Access');
    }
}
