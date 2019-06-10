<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Payment', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedTinyInteger('isPaid')->default(0);
            $table->string('path');
            $table->string('reference');
            $table->string('authorization');
            $table->unsignedTinyInteger('free')->default(0);
            $table->double('price');

            $table->unsignedInteger('payment_type_id');
            $table->foreign('payment_type_id')->references('payment_type_id')->on('Payment_Type');


            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');

            $table->unsignedInteger('admin_id')->nullable()->default(null);
            $table->foreign('admin_id')->references('admin_id')->on('Admin')->onDelete('cascade');

            $table->softDeletes();
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
        Schema::dropIfExists('payment');
    }
}
