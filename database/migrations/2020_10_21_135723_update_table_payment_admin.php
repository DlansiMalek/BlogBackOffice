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
            $table->string('reference')->default(Null)->nullable()->change();
            $table->string('authorization')->default(Null)->nullable()->change();
            $table->string('path')->default(Null)->nullable()->change();

            $table->dropForeign(['pack_admin_id']);
            $table->dropColumn('pack_admin_id');

            $table->unsignedInteger('offre_id')->after('admin_id');
            $table->foreign('offre_id')->references('offre_id')
                ->on('Offre');

            $table->unsignedInteger('payment_type_id')->nullable()->default(null)->after('offre_id');
            $table->foreign('payment_type_id')->references('payment_type_id')
                ->on('Payment_Type')->onDelete('set null');
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
            $table->foreign('pack_admin_id')->references('pack_admin_id')
                ->on('Pack_Admin')->onDelete('cascade');
            $table->dropForeign(['offre_id']);
            $table->dropColumn('offre_id');
            $table->dropForeign(['payment_type_id']);
            $table->dropColumn('payment_type_id');
        });
    }
}
