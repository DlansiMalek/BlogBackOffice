<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Payment_admin', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
            $table->boolean('isPaid');
            $table->string('reference');
            $table->string('authorization');
            $table->double('price')->default(0);
            $table->string('path');
            $table->unsignedInteger('pack_id');
            $table->unsignedInteger('admin_id');
            $table->foreign('pack_id')->references('pack_admin_id')->on('Pack_Admin') ->onDelete('cascade');
            $table->foreign('admin_id')->references('admin_id')->on('Admin') ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_admin', function (Blueprint $table) {
            $table->dropForeign(['pack_id']);
            $table->dropForeign(['admin_id']);
        });
        Schema::dropIfExists('payement_admin');
    }
}
