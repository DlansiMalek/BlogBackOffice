<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Pack_Admin', function (Blueprint $table) {
            $table->increments('pack_admin_id');
            $table->string('name');
            $table->set('type', ['Demo', 'Event','Durée']);
            $table->smallInteger('capacity');           //-1 for unlimitted
            $table->double('price')->default(0);        // 0 if demo
            $table->tinyInteger('nbr_days')->default(0);
            $table->tinyInteger('nbr_events')->default(0);	

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
        Schema::dropIfExists('Pack_Admin');
    }
}
