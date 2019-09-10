<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Organization', function (Blueprint $table) {
            $table->increments('organization_id');
            $table->string('name');
            $table->string('description');
            $table->string('mobile');

            $table->integer('admin_id')->unsigned()->nullable()->default(null);
            $table->foreign('admin_id')->references('admin_id')
                ->on('Admin');

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
        Schema::dropIfExists('Organization');
    }
}
