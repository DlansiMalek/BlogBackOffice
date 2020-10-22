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
            $table->increments("offre_id");
            $table->string('name');
            $table->double('prix')->default(0);
            $table->date('start_date')->default(Null)->nullable();
            $table->date('end_date')->default(Null)->nullable();

            $table->unsignedInteger("type_commission_id")->default(Null)->nullable();
            $table->foreign("type_commission_id")->references('type_commission_id')
                ->on('Type_Commission');
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
            $table->dropForeign(['type_commission_id']);
        });
        Schema::dropIfExists('Offre');
    }
}
