<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAttestationRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Attestation_Request', function (Blueprint $table) {
            $table->increments("attestation_request_id");
            $table->integer("user_id")->unsigned();
            $table->integer("access_id")->unsigned();
            $table->integer("done")->default(0);
            $table->foreign("user_id")->references('user_id')->on('User');
            $table->foreign("access_id")->references('access_id')->on('Access');
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
        //
    }
}
