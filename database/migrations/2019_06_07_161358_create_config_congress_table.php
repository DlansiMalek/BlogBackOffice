<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigCongressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Config_Congress', function (Blueprint $table) {
            $table->increments('config_congress_id');

            $table->string("logo")->nullable()->default(null);
            $table->string("banner")->nullable()->default(null);

            $table->integer("free")->nullable()->default(0);
            $table->unsignedTinyInteger("has_payment")->default(0);

            $table->dateTime('feedback_start')->nullable()->default(null);

            $table->string('program_link')->nullable()->default(null);

            $table->string('voting_token')->nullable()->default(null);

            $table->tinyInteger('nb_ob_access')->nullable()->default(null);

            $table->unsignedTinyInteger('prise_charge_option')->nullable()->default(0);
            $table->unsignedTinyInteger('auto_presence')->default(0);

            $table->string('link_sondage')->nullable()->default(null);
            $table->string('access_system')->default('Ateliers');

            $table->tinyInteger('status')->default(1);

            
            
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
        Schema::table('Config_Congress', function (Blueprint $table) {
            $table->dropForeign(['congress_id']);
        });
        Schema::dropIfExists('Config_Congress');
    }
}
