<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsMailProToTableOffre extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Offre', function (Blueprint $table) {
            $table->boolean('is_mail_pro')->default(0)
                ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Offre', function (Blueprint $table) {
            $table->dropColumn(['is_mail_pro']);
        });
    }
}
