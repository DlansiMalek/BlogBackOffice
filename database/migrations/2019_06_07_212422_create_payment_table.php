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
            $table->string('path')->nullable()->default(null);
            $table->string('reference')
                ->nullable()->default(null);
            $table->string('authorization')
                ->nullable()->default(null);
            $table->unsignedTinyInteger('free')->default(0);
            $table->decimal('price', 10, 3);

            $table->unsignedInteger('payment_type_id')->nullable()->default(null);
            $table->foreign('payment_type_id')->references('payment_type_id')->on('Payment_Type')->onDelete('set null');


            $table->unsignedInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('user_id')->on('User')->onDelete('cascade');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')->onDelete('cascade');


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
        Schema::table('Payment', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['payment_type_id']);
        });
        Schema::dropIfExists('Payment');
    }
}
