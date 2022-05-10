<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoConfigLPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->text('prp_link')->nullable()->default(null);
            $table->text('prp_btn_text')->nullable()->default(null);
            $table->text('prp_btn_text_en')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->removeColumn('prp_link');
            $table->removeColumn('prp_btn_text');
            $table->removeColumn('prp_btn_text_en');
        });
    }
}
