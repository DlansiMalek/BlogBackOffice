<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddColumnCongressType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Congress', function ($table) {
            $table->unsignedInteger('congress_type_id')
                ->unsigned()
                ->nullable()
                ->after('description');

            $table->foreign('congress_type_id')
                ->references('congress_type_id')
                ->on('Congress_Type')
                ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Congress', function ($table) {
            $table->dropForeign(['congress_type_id']);
        });
    }
}
