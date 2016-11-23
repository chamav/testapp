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

        Schema::create('user_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampsTz();
            $table->string('name');
            $table->string('hash', 64)->index();
            $table->bigInteger('size',false, true);
            $table->integer('user_id',false,true)->unsigned()->index();
            $table->boolean('actual')->default('0');
            $table->tinyInteger('type', false, true);
            $table->dateTimeTz('processed')->nullable()->default(null);
            $table->string('mime')->nullable()->default(null);
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
        });
        DB::statement("COMMENT ON column user_files.type IS '0 - image 1 - other';");
        DB::statement("COMMENT ON column user_files.processed IS 'Timestamp processed image';");
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
        Schema::drop('user_files');
    }
}
