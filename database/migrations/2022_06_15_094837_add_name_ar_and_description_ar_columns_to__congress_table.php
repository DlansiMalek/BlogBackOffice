<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameArAndDescriptionArColumnsToCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Congress', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->default(null);
            $table->string('description_ar')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Congress', function (Blueprint $table) {
            $table->removeColumn('name_ar');
            $table->removeColumn('description_ar');
        });
    }
}
