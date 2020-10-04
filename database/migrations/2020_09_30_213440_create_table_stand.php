<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Stand', function (Blueprint $table) {
            $table->increments("stand_id");

            $table->string('name');

            $table->unsignedInteger("congress_id");
            $table->foreign("congress_id")->references('congress_id')->on('Congress')
                ->onDelete('cascade');

            $table->unsignedInteger("organization_id");
            $table->foreign("organization_id")->references('organization_id')->on('Organization')
                ->onDelete('cascade');

            $table->text("url_streaming")
                ->nullable()->default(null);

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

        Schema::table('Stand', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Stand');
    }
}