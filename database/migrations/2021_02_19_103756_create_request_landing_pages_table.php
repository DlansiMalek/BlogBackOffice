<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLandingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Request_Landing_Page', function (Blueprint $table) {
            $table->increments('request_landing_page_id');
            $table->string('dns');
            $table->tinyInteger('status')
                ->nullable()->default(0);
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');
            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('admin_id')
                ->on('Admin')->onDelete('cascade');
          
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
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->dropForeign('congress_id');
            $table->dropForeign('admin_id');
        });
        Schema::dropIfExists('request_landing_pages');
    }
}
