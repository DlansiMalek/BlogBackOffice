<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsStand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('Stand', function (Blueprint $table) {
        $table->string('booth_size')->nullable()->default(null);
        $table->unsignedTinyInteger('priority')
          ->nullable()->default(0);
        $table->string('primary_color')->nullable()->default(null);
        $table->string('secondary_color')->nullable()->default(null);
        $table->boolean('with_products')->nullable()->default(null);
        $table->string('floor_color')->nullable()->default(null);
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('Stand', function (Blueprint $table) {
        $table->dropColumn("booth_size");
        $table->dropColumn("priority");
        $table->dropColumn("primary_color");
        $table->dropColumn("secondary_color");
      });
    }
}
