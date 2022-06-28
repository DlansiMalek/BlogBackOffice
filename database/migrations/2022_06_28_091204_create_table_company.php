<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Company', function (Blueprint $table) {
            $table->increments('company_id');
            $table->string('name');
            $table->string('logo');
            $table->string('description')->nullable()->default(null);
            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')
                ->on('Congress')->onDelete('cascade');
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
        Schema::table('Company', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Company');
    }
}
