<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablePrivilegeConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Privilege_Config', function (Blueprint $table) {
            $table->increments('privilege_config_id');

            $table->unsignedInteger('privilege_id');
            $table->foreign('privilege_id')->references('privilege_id')
                ->on('Privilege');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')
                ->onDelete('cascade');

            $table->unsignedTinyInteger('status')->default(1);
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
        Schema::table('Privilege_Config', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['privilege_id']);
        });
        Schema::dropIfExists('Privilege_Config');
    }
}
