<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Congress', function (Blueprint $table) {
            $table->increments('congress_id');
            $table->string('name');
            $table->date('date');

            $table->string("username_mail")->nullable()->default(null);
            $table->string("logo")->nullable()->default(null);
            $table->string("banner")->nullable()->default(null);

            $table->integer("price")->nullable()->default(null);
            $table->integer("free")->nullable()->default(null);

            $table->integer('admin_id')->unsigned();
            $table->foreign('admin_id')->references('admin_id')->on('Admin')
                ->onDelete('cascade');

            $table->boolean("has_paiement")->default(false);

            $table->dateTime('feedback_start')->nullable()->default(null);


            $table->string("program_congress")->nullable()->default(null);
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
        Schema::dropIfExists('Congress');
    }
}
