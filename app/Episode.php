<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model {
    public $table = 'episodes';
    
    protected $fillable = [
        'name',
        'overview',
        'movie_db_id',
        'air_date',
        'season_number',
        'episode_number',
        'kinopoisk_id',
        'still_path',
        'still_path_thumb'  
    ];
}