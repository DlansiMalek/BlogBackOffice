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
            $table->boolean('organization_accepted')->default(false);
            $table->tinyInteger('email_verified')->default(0);

            $table->string('verification_code')
                ->nullable();

            $table->string('qr_code');
            $table->tinyInteger('isPresent')->unsigned()->default(0);
            $table->tinyInteger('isPaied')->unsigned()->default(0);

            $table->string("path_payement")->nullable()->default(null);
            $table->string("ref_payment")->nullable()->default(null);
            $table->string("autorisation_num")->nullable()->default(null);

            $table->tinyInteger("email_sended")->default(0);
            $table->tinyInteger("email_attestation_sended")->default(0);

            $table->string("rfid")->nullable()->default(null);


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


            #labo prise en charge
            $table->integer('organization_id')->unsigned()->nullable();
            $table->foreign('organization_id')->references('organization_id')->on('Organization')
                ->onDelete('cascade');


            #foreign Pack
            $table->integer('pack_id')->unsigned()->nullable();
            $table->foreign('pack_id')->references('pack_id')->on('Pack')
                ->onDelete('cascade');

            #foreign Pack
            $table->integer('privilege_id')->unsigned()->default(3);
            $table->foreign('privilege_id')->references('privilege_id')->on('Privilege')
                ->onDelete('cascade');

            #foreign Country
            $table->integer('country_id')->unsigned()->nullable()->default(null);
            $table->foreign('country_id')->references('country_id')->on('Country')
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
        Schema::dropIfExists('User');
    }
}
