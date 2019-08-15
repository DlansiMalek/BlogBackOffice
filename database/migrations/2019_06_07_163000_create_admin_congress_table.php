<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Admin_Congress', function (Blueprint $table) {
            $table->increments('admin_congress_id');

            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('admin_id')->on('Admin');

            $table->unsignedInteger('congress_id');
            $table->foreign('congress_id')->references('congress_id')->on('Congress')
            ->onDelete('cascade');
            $table->unsignedInteger('organization_id')->nullable()->default(null);
            $table->foreign('organization_id')->references('organization_id')->on('Organization');

            $table->unsignedInteger('privilege_id');
            $table->foreign('privilege_id')->references('privilege_id')->on('Privilege');

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
        Schema::table('Admin_Congress', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['privilege_id']);
        });
        Schema::dropIfExists('Admin_Congress');
    }
}
