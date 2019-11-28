<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttestationRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Attestation_Request', function (Blueprint $table) {
            $table->increments('attestation_request_id');

            $table->tinyInteger('done')
                ->default(0);


            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('user_id')->on('User');

            $table->unsignedInteger('congress_id')->nullable()->default(null);
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
            ->onDelete('cascade');
            $table->unsignedInteger('access_id')->nullable()->default(null);
            $table->foreign('access_id')->references('access_id')->on('Access');


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
        Schema::table('Attestation_Request', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['access_id']);
        });
        Schema::dropIfExists('Attestation_Request');
    }
}
