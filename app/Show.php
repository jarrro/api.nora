<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Show extends Model {
    public $table = 'shows';
    
    protected $fillable = [
        'kinopoisk_id',
        'movie_db_id',
        'name_ru',
        'name_en',
        'year',
        'country',
        'tagline',
        'genre',
        'budget',
        'premier',
        'premier_rus',
        'description',
        'trivia',
        'trivia_blooper',
        'poster_path',
        'poster_path_thumb',
        'backdrop_path',
        'backdrop_path_thumb',
        'trailer',
        'trailer_duration',
        'age_limit',
        'time_film',
        'rating',
        'studio'
    ];
}