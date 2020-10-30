<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableOffre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Offre', function (Blueprint $table) {
            $table->increments('offre_id');
            $table->string('nom');
            $table->double('value')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer("status");

            $table->unsignedInteger("type_id");
            $table->foreign("type_id")->references('type_id')
                ->on('Type');

            $table->unsignedInteger('admin_id');
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
        Schema::table('Offre', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['type_id']);
        });
        Schema::dropIfExists('Offre');
    }
}
