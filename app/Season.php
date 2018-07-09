<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model {
    public $table = 'seasons';
    
    protected $fillable = [
        'name',
        'overview',
        'season_number',
        'air_date',
        'movie_db_id',
        'kinopoisk_id',
        'poster_path',
        'poster_path_thumb'
    ];
}