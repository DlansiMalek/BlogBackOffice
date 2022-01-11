<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelEnToTableFormInput extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Form_Input', function (Blueprint $table) {
            $table->text('label_en')->nullable()->default(null);
            $table->text('label')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Form_Input', function (Blueprint $table) {
            $table->removeColumn('label_en');
            $table->string('label')->change();
        });
    }
}
