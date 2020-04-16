<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Submission', function (Blueprint $table) {
            $table->bigIncrements('submission_id');
            $table->string('title');
            $table->string('type');
            $table->string('prez_type');
            $table->text('description');
            $table->integer('global_note')->default(0);
            $table->integer('status')->default(0);

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('User')
                ->onDelete('cascade');

            $table->unsignedInteger('theme_id')->nullable();
            $table->foreign('theme_id')->references('theme_id')->on('Theme')
                ->onDelete('set null');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
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
        Schema::table('Submission', function ($table) {
            $table->dropForeign(['theme_id']);
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('Submission');


    }
}
