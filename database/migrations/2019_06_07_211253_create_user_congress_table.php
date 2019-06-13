<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('User_Congress', function (Blueprint $table) {
            $table->increments('user_congress_id');
            $table->unsignedTinyInteger('isPresent')->default(0);
            $table->unsignedTinyInteger('organization_accepted')->default(0);

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('privilege_id');
            $table->foreign('privilege_id')->references('privilege_id')->on('Privilege')->onDelete('cascade');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');

            $table->unsignedInteger('organization_id')->nullable()->default(null);
            $table->foreign('organization_id')->references('organization_id')->on('Organization')->onDelete('cascade');

            $table->unsignedInteger('pack_id')->nullable()->default(null);
            $table->foreign('pack_id')->references('pack_id')->on('Pack');

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
        Schema::dropIfExists('user_congress');
    }
}
