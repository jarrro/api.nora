<?php
namespace App\Http\Controllers\Show;

use App\Season;

use App\Help;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;

class SeasonController extends Controller {

    public function create() {
        $KID = $_GET['kinopoisk_id'];
        $SEASON = $_GET['season'];
        $help = new Help();
        $s = new Season();

        if(Season::where('kinopoisk_id', $KID)
            ->where('season_number', $SEASON)
            ->get() == '[]') 
        {

            $getmovie = Help::getHttpRequest("https://getmovie.cc/api/kinopoisk.json?id={$KID}&token=".Help::GETM);
            $tmdb = Help::getHttpRequest('https://api.themoviedb.org/3/search/tv?api_key='.Help::TMDB.'&language=ru&query='.urlencode($getmovie['name_en']).'&page=1')['results'][0];
            $seasonInfo = Help::getHttpRequest("https://api.themoviedb.org/3/tv/".$tmdb['id']."/season/{$SEASON}?api_key=".Help::TMDB."&language=ru-RU");

            $structure = "catalog/season/{$KID}/";
            $manager = new ImageManager(); 
            @mkdir($structure, 0777, true);  
                
            if(!file_exists("catalog/season/{$KID}/poster_path.jpg")) {
                
                $backdrop = $manager->make("https://image.tmdb.org/t/p/original".$seasonInfo['poster_path']);
                $backdrop->save("catalog/season/{$KID}/poster_path.jpg");
                $backdrop->resize(300, null, function($constraint) {
                    $constraint->aspectRatio();
                });
                $backdrop->save("catalog/season/{$KID}/poster_path_thumb.jpg");
            }

            $s->name = $seasonInfo['name'];
            $s->overview = $seasonInfo['overview'];
            $s->movie_db_id = $seasonInfo['id'];
            $s->air_date = $seasonInfo['air_date'];
            $s->season_number = $seasonInfo['season_number'];
            $s->kinopoisk_id = $KID;
            $s->poster_path = "catalog/season/{$KID}/poster_path.jpg";
            $s->poster_path_thumb = "catalog/season/{$KID}/poster_path_thumb.jpg";
            
            try {
                $s->save();
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            }    

            return response()->json(Season::findOrFail($s->id)); 
        }
    }

    public function get() {
        $s = new Season();
        $SEASON = $_GET['season'];
        $KID = $_GET['kinopoisk_id'];
        
        return Season::where('kinopoisk_id', $KID)
        ->where('season_number', $SEASON)
        ->get();
    }
 
}