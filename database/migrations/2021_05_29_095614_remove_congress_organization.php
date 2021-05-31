<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveCongressOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // migrate existing data
      $pathDB = public_path('db/move_data_congress_organization.sql');
      DB::unprepared(file_get_contents($pathDB));
      Schema::dropIfExists('Congress_Organization');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
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
}
