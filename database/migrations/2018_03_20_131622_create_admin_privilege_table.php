<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPrivilegeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Admin_Privilege', function (Blueprint $table) {
            $table->increments('admin_privilege_id');


            $table->integer('admin_id')->unsigned();
            $table->foreign('admin_id')->references('admin_id')->on('Admin')
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
        Schema::dropIfExists('Admin_Privilege');
    }
}
