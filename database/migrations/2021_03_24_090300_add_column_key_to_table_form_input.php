<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnKeyToTableFormInput extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Form_Input', function (Blueprint $table) {
            $table->string('key')
                ->nullable()
                ->default(null)
                ->after('label');
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
            $table->removeColumn('key');
        });
    }
}
