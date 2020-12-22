<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCongressOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Congress_Organization', function (Blueprint $table) {
            $table->increments("congress_organization_id");


            $table->unsignedInteger('organization_id');
            $table->foreign("organization_id")
                ->references('organization_id')->onDelete('cascade')
                ->on('Organization');


            $table->unsignedInteger('congress_id');
            $table->foreign("congress_id")
                ->references('congress_id')->onDelete('cascade')
                ->on('Congress');


            $table->double('montant')->default(0);


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
        Schema::table('Congress_Organization', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['organization_id']);
        });
        Schema::dropIfExists('Congress_Organization');
    }
}