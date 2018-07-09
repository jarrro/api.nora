<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('overview');
            $table->string('translator');
            $table->date('air_date');
            $table->integer('season_number');
            $table->integer('episode_number');
            $table->integer('movie_db_id');
            $table->integer('kinopoisk_id');
            $table->string('still_path');
            $table->string('still_path_thumb');
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
        Schema::dropIfExists('episodes');
    }
}
