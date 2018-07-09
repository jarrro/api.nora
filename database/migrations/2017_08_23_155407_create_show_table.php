<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_ru');
            $table->string('title_en');
            $table->integer('year');
            $table->integer('kinopoisk_id');
            $table->integer('movie_db_id');
            $table->integer('world_art_id');            
            $table->dateTime('last_episode_time');
            $table->dateTime('last_update');
            $table->string('tagline');
            $table->text('description');
            $table->integer('age');
            $table->text('countries');
            $table->text('genres');
            $table->text('actors');
            $table->text('directors');
            $table->string('poster_path');
            $table->string('poster_path_thumb');
            $table->string('backdrop_path');
            $table->string('backdrop_path_thumb');
            $table->text('studios');
            $table->float('kinopoisk_rating');
            $table->integer('kinopoisk_votes');
            $table->float('imdb_rating');
            $table->integer('imdb_votes');
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
        Schema::dropIfExists('shows');
    }
}
