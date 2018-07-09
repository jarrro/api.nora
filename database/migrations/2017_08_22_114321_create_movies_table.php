<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title_ru');
            $table->string('title_en');
            $table->integer('year');
            $table->integer('duration');
            $table->integer('kinopoisk_id');
            $table->integer('world_art_id');
            $table->integer('movie_db_id');
            $table->boolean('camrip');
            $table->string('source_type');            
            $table->boolean('instream_ads');            
            $table->string('tagline');
            $table->text('description');
            $table->string('age_limit');
            $table->text('countries');            
            $table->text('genries');
            $table->text('actors');
            $table->text('directors');
            $table->string('poster_path');
            $table->string('poster_path_thumb');
            $table->string('backdrop_path');
            $table->string('backdrop_path_thumb');
            $table->string('trailer');
            $table->text('studios');   
            $table->float('kinopoisk_rating');
            $table->string('kinopoisk_votes');
            $table->float('imdb_rating');
            $table->string('imdb_votes');
            $table->string('iframe_url');
            $table->integer('added_user');
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
        Schema::dropIfExists('movies');
    }
}
