<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValueArColumnToFormInputValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Form_Input_Value', function (Blueprint $table) {
            $table->string('value_ar')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Form_Input_Value', function (Blueprint $table) {
            $table->removeColumn('value_ar');
        });
    }
}
