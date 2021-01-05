<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStandResource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Resource_Stand', function (Blueprint $table) {
            $table->increments("resource_stand_id");

            $table->string("doc_name")
                ->nullable()->default(null);

            $table->unsignedInteger("stand_id");
            $table->foreign("stand_id")->references('stand_id')->on('Stand')
                ->onDelete('cascade');

            $table->unsignedInteger("resource_id");
            $table->foreign("resource_id")->references('resource_id')->on('Resource')
                ->onDelete('cascade');

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
            $table->dropForeign(['stand_id']);
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Resource_Stand');
    }
}
