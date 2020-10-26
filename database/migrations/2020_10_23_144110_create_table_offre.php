<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableOffre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Offre', function (Blueprint $table) {
            $table->increments('offre_id');
            $table->double('prix_unitaire')->default(0);
            $table->date('start_date')->default(Null)->nullable();
            $table->date('end_date')->default(Null)->nullable();

            $table->unsignedInteger("type_commission_id")->default(Null)->nullable();
            $table->foreign("type_commission_id")->references('type_commission_id')
                ->on('Type_Commission');

            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('admin_id')->on('Admin')
                ->onDelete('cascade');

            $table->unsignedInteger('type_offre_id');
            $table->foreign('type_offre_id')->references('type_offre_id')
                ->on('Type_Offre');

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
        Schema::table('Offre', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['type_commission_id']);
            $table->dropForeign(['type_offre_id']);
        });
        Schema::dropIfExists('Admin_Offre');
    }
}
