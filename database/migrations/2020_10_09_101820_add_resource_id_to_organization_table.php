
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResourceIdToOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Organization', function (Blueprint $table) {
            $table->unsignedInteger("resource_id")->nullable()->default(null);
            $table->foreign("resource_id")->references('resource_id')->on('Resource')
                ->onDelete('cascade');

            $table->string('description')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Organization', function (Blueprint $table) {
            $table->dropForeign(['resource_id']);
        });
    }
}
