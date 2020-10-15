<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Tracking', function (Blueprint $table) {
            $table->increments('tracking_id');

            $table->dateTime("date");
            $table->string("type")->nullable()->default(null);

            $table->text("comment")
                ->nullable()->default(null);

            $table->unsignedInteger("action_id");
            $table->foreign("action_id")->references('action_id')->on('Action')
                ->onDelete('cascade');

            $table->unsignedInteger("access_id")->nullable()->default(null);
            $table->foreign("access_id")->references('access_id')->on('Access')
                ->onDelete('set null');

            $table->unsignedInteger("stand_id")->nullable()->default(null);
            $table->foreign("stand_id")->references('stand_id')->on('Stand')
                ->onDelete('set null');

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
        Schema::table('Resource_Stand', function (Blueprint $table) {
            $table->dropForeign(['action_id']);
            $table->dropForeign(['access_id']);
            $table->dropForeign(['stand_id']);
        });
        Schema::dropIfExists('Tracking');
    }
}
