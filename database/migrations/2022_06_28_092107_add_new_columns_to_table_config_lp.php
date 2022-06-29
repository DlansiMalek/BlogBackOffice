<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToTableConfigLp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Config_LP', function (Blueprint $table) {
            $table->string('companies_title')->nullable()->default(null);
            $table->text('companies_description')->nullable()->default(null);
            $table->string('companies_title_en')->nullable()->default(null);
            $table->text('companies_description_en')->nullable()->default(null);
            $table->string('companies_title_ar')->nullable()->default(null);
            $table->text('companies_description_ar')->nullable()->default(null);
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
            $table->removeColumn('companies_title');
            $table->removeColumn('companies_description');
            $table->removeColumn('companies_title_en');
            $table->removeColumn('companies_description_en');
            $table->removeColumn('companies_title_ar');
            $table->removeColumn('companies_description_ar');
        });
    }
}
