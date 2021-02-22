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
        Schema::create('request_landing_pages', function (Blueprint $table) {
            $table->increments('request_landing_page_id');
            $table->string('dns');
            $table->tinyInteger('status')->default('0');
            $table->unsignedInteger('congress_id')->nullable()->default(null);
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');;
            $table->integer('admin_id')->unsigned()->nullable()->default(null);
            $table->foreign('admin_id')->references('admin_id')
                ->on('Admin');
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
        Schema::dropIfExists('request_landing_pages');
    }
}
