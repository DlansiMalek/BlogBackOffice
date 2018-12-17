<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Congress', function (Blueprint $table) {
            $table->increments('congress_id');
            $table->string('name');
            $table->date('date');

            $table->integer('admin_id')->unsigned();
            $table->foreign('admin_id')->references('admin_id')->on('Admin')
                ->onDelete('cascade');
            $table->text("mail_inscription")->nullable()->default(null);
            $table->text("mail_payement")->nullable()->default(null);


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
        Schema::dropIfExists('Congress');
    }
}
