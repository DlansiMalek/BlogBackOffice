<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('gender')->nullable();
            $table->string('mobile');
            $table->string('email');
            $table->tinyInteger('email_verified')->default(0);

            $table->string('verification_code')
                ->nullable();

            $table->string('qr_code');
            $table->tinyInteger('isPresent')->unsigned()->default(0);

            $table->tinyInteger('isPaied')->unsigned()->default(0);

            # champs calculé
            $table->double('price')
                ->nullable();

            #foreign congressId
            $table->integer('congress_id')->unsigned();
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
                ->onDelete('cascade');

            #foreign payement
            $table->integer('payement_type_id')->unsigned()->nullable();
            $table->foreign('payement_type_id')->references('payement_type_id')->on('Payement_Type')
                ->onDelete('cascade');


            #foreign payement
            $table->integer('grade_id')->unsigned()->nullable();
            $table->foreign('grade_id')->references('grade_id')->on('Grade')
                ->onDelete('cascade');

            #foreign payement
            $table->integer('lieu_ex_id')->unsigned()->nullable();
            $table->foreign('lieu_ex_id')->references('lieu_ex_id')->on('Lieu_Ex')
                ->onDelete('cascade');

            #labo prise en charge
            $table->integer('labo_id')->unsigned()->nullable();
            $table->foreign('labo_id')->references('labo_id')->on('Labo')
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
        Schema::dropIfExists('User');
    }
}
