<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateRecaptchaAuthFailuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('recaptcha_auth_failures', function (Blueprint $table) {
            $table->id('Id');
            $table->integer('UserId')->nullable()->default(null); // May be null if user not exists in system at the time of login failure
            $table->string('Email')->default('');
            $table->string('IpAddress', 45)->default(''); // Both IP v4 and v6 are supported
            $table->integer('ErrorLoginsCount')->default(0);
            $table->integer('Time')->default(0);
            $table->timestamp(\Aurora\System\Classes\Model::CREATED_AT)->nullable();
            $table->timestamp(\Aurora\System\Classes\Model::UPDATED_AT)->nullable();

            $table->unique(['Email', 'IpAddress']);
            $table->index('Time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('recaptcha_auth_failures');
    }
}
