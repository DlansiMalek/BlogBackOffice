<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCongressOrganization extends Migration
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
                ->references('organization_id')
                ->on('Organization');


            $table->unsignedInteger('congress_id');
            $table->foreign("congress_id")
                ->references('congress_id')
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
        Schema::dropIfExists('congress_organization');
    }
}
