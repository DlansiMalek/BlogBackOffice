<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLpOrganizer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('LP_Organizer', function (Blueprint $table) {
            $table->increments('lp_organizer_id');
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')->onDelete('cascade');
            $table->string('full_name');
            $table->string('role');
            $table->string('profile_img')->nullable()->default(null);
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
        Schema::table('LP_Organizer', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('LP_Organizer');
    }
}
