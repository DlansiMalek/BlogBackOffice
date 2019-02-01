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
            $table->increments("congress_orgranization_id");
            $table->integer("organization_id")->unsigned();
            $table->integer("congress_id")->unsigned();
            $table->float("montant")->default(0);
            $table->foreign("organization_id")->references('organization_id')->on('Organization');
            $table->foreign("congress_id")->references('congress_id')->on('Congress');
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
        //
    }
}
