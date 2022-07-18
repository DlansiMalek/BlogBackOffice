<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelArToTableFormInput extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Form_Input', function (Blueprint $table) {
            $table->text('label_ar')->nullable()->default(null);
            $table->text('public_label_ar')->nullable()->default(null);
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
            $table->removeColumn('label_ar');
            $table->removeColumn('public_label_ar');
        });
    }
}
