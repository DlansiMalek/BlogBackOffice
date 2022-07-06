<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArabicColumnsToTableAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Access', function (Blueprint $table) {
            $table->string('name_en')->nullable()->default(null);
            $table->string('name_ar')->nullable()->default(null);
            $table->string('description_en')->nullable()->default(null);
            $table->string('description_ar')->nullable()->default(null);
            $table->string('name')->nullable()->default(null)->change();
            $table->text('description')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Access', function (Blueprint $table) {
            $table->removeColumn('name_en');
            $table->removeColumn('name_ar');
            $table->removeColumn('description_en');
            $table->removeColumn('description_ar');   
            $table->string('name')->change();
            $table->string('description')->change();       
        });
    }
}
