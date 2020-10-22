<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTablePaymentAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Payment_Admin', function (Blueprint $table) {
            $table->dropColumn('reference');
            $table->dropColumn('authorization');
            $table->dropColumn('path');
            $table->dropForeign(['pack_admin_id']);
            $table->dropColumn('pack_admin_id');
            $table->date('deadline')->default(Null)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Payment_Admin', function (Blueprint $table) {
            $table->string('reference');
            $table->string('authorization');
            $table->string('path');
            $table->foreign('pack_admin_id')->references('pack_admin_id')
                ->on('Pack_Admin')->onDelete('cascade');
            $table->dropColumn("deadline");
        });
    }
}
