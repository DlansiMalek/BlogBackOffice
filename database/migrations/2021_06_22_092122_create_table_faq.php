<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFaq extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('FAQ', function (Blueprint $table) {
            $table->increments("faq_id");
            $table->unsignedInteger('stand_id');
            $table->foreign("stand_id")
                ->references('stand_id')
                ->on('Stand')->onDelete('cascade');
            $table->string('question')->nullable()->default(null);
            $table->text('response')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('FAQ', function (Blueprint $table) {
            $table->dropForeign(['stand_id']);
        });
        Schema::dropIfExists('FAQ');
    }
}
