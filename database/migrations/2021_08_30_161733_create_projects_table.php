<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Project', function (Blueprint $table) {
            $table->increments('project_id');
            $table->string('nom');
            $table->date('date');
            $table->string("project_img")->nullable()->default(null);
            $table->string('lien')->nullable()->default(null);
            $table->integer('admin_id')->unsigned()->nullable()->default(null);
            $table->foreign('admin_id')->references('admin_id')
                ->on('Admin');
            $table->integer('category_id')->unsigned()->nullable()->default(null);
            $table->foreign('category_id')->references('category_id')->onDelete('cascade')
                ->on('Category');
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
        Schema::table('Project', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['category_id']);
        });
        Schema::dropIfExists('Project');
    }
}
