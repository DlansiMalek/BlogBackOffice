<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Module', function (Blueprint $table) {
            $table->increments('module_id');
            $table->set('type', ['Participants', 'E-mailing','Badge','Organization ','Vote','Sondage','All']);
            $table->text('description')->nullable()->default(null);

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
        Schema::dropIfExists('Module');
    }
}
