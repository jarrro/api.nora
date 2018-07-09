<?php
namespace App\Http\Controllers\Show;

use App\Episode;

use App\Help;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;

class EpisodeController extends Controller {

    public function create(Request $request) {
        $KID = $request->input('kinopoisk_id');
        $SEASON = $request->input('season');
        $EPISODE = $request->input('episode');
        $TRANSLATOR = $request->input('translator_id');
        $help = new Help();
        $ep = new Episode();

        if(Episode::where('kinopoisk_id', $KID)
            ->where('season_number', $SEASON)
            ->where('episode_number', $EPISODE)
            ->get() == '[]') 
        {
            
            $moon = Help::getHttpRequest('http://moonwalk.cc/api/videos.json?kinopoisk_id='.$KID.'&api_token='.Help::MOON);
            $tmdb = Help::getHttpRequest('https://api.themoviedb.org/3/search/tv?api_key='.Help::TMDB.'&language=ru&query='.urlencode($moon[0]['title_en']).'&page=1')['results'][0];
            $epInfo = Help::getHttpRequest("https://api.themoviedb.org/3/tv/".$tmdb['id']."/season/{$SEASON}/episode/{$EPISODE}?api_key=".Help::TMDB."&language=ru-RU");

            $translator_name = '';
            for($i=0; $i<count($moon); $i++) {
                if($moon[$i]['translator_id'] == $TRANSLATOR) {
                    $translator_name = $moon[$i]['translator'];
                    break;
                }
            }

            $moon = $moon[0];

            $structure = "catalog/episode/{$KID}/";
            $manager = new ImageManager(); 
            @mkdir($structure, 0777, true);  
                
            if(!file_exists("catalog/episode/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-s{$SEASON}-e{$EPISODE}.jpg")) {
                
                $backdrop = $manager->make("https://image.tmdb.org/t/p/original".$epInfo['still_path']);
                $backdrop->save("catalog/episode/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-s{$SEASON}-e{$EPISODE}.jpg");
                $backdrop->resize(300, null, function($constraint) {
                    $constraint->aspectRatio();
                });
                $backdrop->save("catalog/episode/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-s{$SEASON}-e{$EPISODE}_thumb.jpg");
            }

            $ep->name = $epInfo['name'];
            $ep->overview = $epInfo['overview'];
            $ep->movie_db_id = $epInfo['id'];
            $ep->air_date = $epInfo['air_date'];
            $ep->season_number = $epInfo['season_number'];
            $ep->episode_number = $epInfo['episode_number'];
            $ep->translator = $translator_name;
            $ep->kinopoisk_id = $KID;
            $ep->still_path = "catalog/episode/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-s{$SEASON}-e{$EPISODE}.jpg";
            $ep->still_path_thumb = "catalog/episode/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-s{$SEASON}-e{$EPISODE}_thumb.jpg";
            
            try {
                $ep->save();
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            }    

            // return response()->json(Episode::findOrFail($ep->id));  
            return redirect()->route('episode', ['kinopoisk_id' => $KID, 'season' => $SEASON, 'episode' => $EPISODE, 'translator_id' => $TRANSLATOR]);
        } else {
            return redirect()->route('episode', ['kinopoisk_id' => $KID, 'season' => $SEASON, 'episode' => $EPISODE, 'translator_id' => $TRANSLATOR]);
        }

    }

    public function getEpisode(Request $request) {
        $episode = new Episode();
        $SEASON = $request->input('season');
        $EPISODE = $request->input('episode');
        $KID = $request->input('kinopoisk_id');
        $TRANSLATOR = $request->input('translator_id');
        
        
        
        return DB::table('episodes')
        ->join('show_translators', 'episodes.kinopoisk_id', 'show_translators.kinopoisk_id')
        ->where('episodes.kinopoisk_id', $KID)
        ->where('show_translators.translator_id', $TRANSLATOR)
        ->where('episodes.season_number', $SEASON)
        ->where('episodes.episode_number', $EPISODE)
        ->get();
    }

    public function getEpidesCount() {
        $help = new Help();
        $KID = $_GET['kinopoisk_id'];
        $TRANSLATOR = $_GET['translator_id'];

        $moon = Help::getHttpRequest("http://moonwalk.cc/api/serial_episodes.json?kinopoisk_id={$KID}&translator_id={$TRANSLATOR}&api_token=".Help::MOON);
        return response()->json($moon);
    }

    public function lastAdded() {
        return DB::table('episodes')
        ->join('shows', 'episodes.kinopoisk_id', 'shows.kinopoisk_id')
        ->select('episodes.name', 'episodes.season_number', 'episodes.still_path_thumb', 'episodes.episode_number', 'episodes.translator', 'shows.poster_path_thumb')
        ->orderBy('episodes.id', 'desc')
        ->limit(12)
        ->get();
    }
}