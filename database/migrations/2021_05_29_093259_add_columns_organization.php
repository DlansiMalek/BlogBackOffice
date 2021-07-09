<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Organization', function (Blueprint $table) {
          $table->string('email')->nullable()->default(null);
          $table->string('banner')->nullable()->default(null);
          $table->string('logo')->nullable()->default(null);
          $table->string('website_link')->nullable()->default(null);
          $table->string('twitter_link')->nullable()->default(null);
          $table->string('linkedin_link')->nullable()->default(null);
          $table->string('fb_link')->nullable()->default(null);
          $table->string('insta_link')->nullable()->default(null);
          $table->double('montant')->default(0);

          $table->unsignedInteger("congress_id")->nullable()->default(null);
          $table->foreign("congress_id")->references('congress_id')->on('Congress')
              ->onDelete('cascade');

          $table->dropForeign(['resource_id']);
          $table->dropColumn('resource_id');
          $table->dropColumn('logo_position');
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
        $table->unsignedInteger("resource_id")->nullable()->default(null);
        $table->foreign("resource_id")->references('resource_id')->on('Resource')
            ->onDelete('cascade');

        $table->string('logo_position')->nullable()->default(null);

        $table->dropForeign(['congress_id']);
        $table->dropColumn('congress_id');

        $table->dropColumn("montant");
        $table->dropColumn("logo");
        $table->dropColumn("banner");
        $table->dropColumn("email");
        $table->dropColumn("website_link");
        $table->dropColumn("fb_link");
        $table->dropColumn("insta_link");
        $table->dropColumn("twitter_link");
        $table->dropColumn("linkedin_link");
      });
    }
}
