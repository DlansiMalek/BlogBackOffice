<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Author', function (Blueprint $table) {
            $table->increments('author_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('rank');

            $table->unsignedBigInteger('submission_id');
            $table->foreign('submission_id')->references('submission_id')->on('Submission')
                ->onDelete('cascade');
            
                $table->unsignedInteger('service_id')->nullable()->default(null);
                $table->foreign('service_id')->references('service_id')->on('Service')
                ->onDelete('cascade');

                $table->unsignedInteger('etablissement_id')->nullable()->default(null);
                $table->foreign('etablissement_id')->references('etablissement_id')->on('Etablissement')
                ->onDelete('cascade');

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
        Schema::dropIfExists('Author');
    }
}
