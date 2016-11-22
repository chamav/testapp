<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            /*KEY `name_en` (`name_en`)*/
            $table->increments('id');
            $table->mediumInteger('region_id');
            $table->string('name_ru', 128);
            $table->string('name_en', 128);
            $table->decimal('lat', 10, 5);
            $table->decimal('lon', 10, 5);
            $table->string('okato', 20);

            $table->index('name_en');
        });

        DB::statement("COMMENT ON TABLE cities IS 'Информация о городах';");

        $file = 'sxgeo__cities.sql';
        $path = '.'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR;

        DB::unprepared(file_get_contents($path.$file));
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestampsTz();
            $table->tinyInteger('age');
            $table->tinyInteger('weight');
            $table->integer('city_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->enum('sex', ['0', '1', '2'])->nullable()->default(null)->comment('1-female, 2- male');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
        Schema::drop('cities');
    }
}
