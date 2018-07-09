<?php
namespace App\Http\Controllers\Show;

use App\Show;
use App\Person;
use App\Meta;

use App\Help;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Input;

class ShowController extends Controller {
    public function create(Request $request, $id) {
        $help = new Help();
        $show = new Show();
        $person = new Person();        
        $meta = new Meta();

        // $KID = $_GET['kinopoisk_id'];  

        // $KID = Input::get('kinopoisk_id');

        $KID = $id;

        if(Show::where('kinopoisk_id', $KID)->get() == '[]') {        
            $moon = Help::getHttpRequest('http://moonwalk.cc/api/videos.json?kinopoisk_id='.$KID.'&api_token='.Help::MOON);
            $tmdb = Help::getHttpRequest('https://api.themoviedb.org/3/search/tv?api_key='.Help::TMDB.'&language=ru&query='.urlencode($moon[0]['title_en']).'&page=1')['results'][0];


            $translators_array = array();
            for($i=0; $i < count($moon); $i++) {
                if(DB::table('show_translators')
                    ->where('translator_id', $moon[$i]['translator_id'])
                    ->where('kinopoisk_id', $moon[$i]['kinopoisk_id'])
                    ->get()
                    == '[]'
                    ) {
                    array_push($translators_array, array(
                        'translator_id' => $moon[$i]['translator_id'],
                        'kinopoisk_id' => $moon[$i]['kinopoisk_id'],
                        'translator' => $moon[$i]['translator'],
                        'iframe_url' => $moon[$i]['iframe_url']
                    ));
                }
            }

            $moon = $moon[0];

            if($request->cookie('user_id')) {
                $user = $request->cookie('user_id');
            } else {
                $user = 0;
            }

            $structure = "catalog/show/{$KID}/";
            $manager = new ImageManager(); 
            @mkdir($structure, 0777, true);  
                
            if(!file_exists("catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path.jpg") && $tmdb['backdrop_path'] != '') {
                
                $backdrop = $manager->make("https://image.tmdb.org/t/p/original".$tmdb['backdrop_path']);
                $backdrop->save("catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path.jpg");
                $backdrop->resize(300, null, function($constraint) {
                    $constraint->aspectRatio();
                });
                $backdrop->save("catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path-thumb.jpg");
            }

            if(!file_exists("catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path.jpg") && $tmdb['poster_path'] != '') {
                $poster = $manager->make("https://image.tmdb.org/t/p/original".$tmdb['poster_path']);
                $poster->save("catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path.jpg");
                $poster->resize(300, null, function($constraint) {
                    $constraint->aspectRatio();
                });
                $poster->save("catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path-thumb.jpg");              
            }  

            $show->kinopoisk_id = $KID;
            $show->movie_db_id = $tmdb['id'];
            $show->title_ru = $this::ifNotNull($moon['title_ru']);
            $show->title_en = $this::ifNotNull($moon['title_en']);
            $show->year = $this::ifNotNull($moon['year']);
            $show->movie_db_id = $tmdb['id'];
            $show->world_art_id = $this::ifNotNull($moon['world_art_id']);
            $show->last_episode_time = $this::ifNotNull($moon['last_episode_time']);
            $show->last_update = $this::ifNotNull($moon['material_data']['updated_at']);
            $show->tagline = $this::ifNotNull($moon['material_data']['tagline']);
            $show->description = $this::ifNotNull($moon['material_data']['description']);
            $show->age = $this::ifNotNull($moon['material_data']['age']);
            $show->countries = $this::ifNotNull(json_encode($moon['material_data']['countries']));
            $show->genres = $this::ifNotNull(json_encode($moon['material_data']['genres']));
            $show->actors = $this::ifNotNull(json_encode($moon['material_data']['actors']));
            $show->directors = $this::ifNotNull(json_encode($moon['material_data']['directors']));                      
            $show->poster_path = "catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path.jpg";
            $show->backdrop_path = "catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path.jpg";            
            $show->poster_path_thumb = "catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path-thumb.jpg";
            $show->backdrop_path_thumb = "catalog/show/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path-thumb.jpg";
            // TRAILER
            $show->studios = $this::ifNotNull(json_encode($moon['material_data']['studios']));    
            $show->kinopoisk_rating = $this::ifNotNull($moon['material_data']['kinopoisk_rating']);
            $show->kinopoisk_votes = $this::ifNotNull($moon['material_data']['kinopoisk_votes']);
            $show->imdb_rating = $this::ifNotNull($moon['material_data']['imdb_rating']);
            $show->imdb_votes = $this::ifNotNull($moon['material_data']['imdb_votes']);
            /***** */

            /**/
            try {
                DB::table('show_translators')->insert($translators_array);
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            } 

            /**/
            try {
                $show->save();
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            }    

            $show_name = $moon['title_ru'];
            $show_year = $moon['year'];

            $meta->id_material = $show->id;
            $meta->kinopoisk_id = $KID;
            $meta->title = "{$show_name} ({$show_year}): Смотреть сериал бесплатно в качестве 720";
            $meta->description = strip_tags($moon['material_data']['description']);
            $meta->keywords = "сериал {$show_name}, {$show_name} {$show_year}, {$show_name} смотреть онлайн, {$show_name} в хорошем качестве";
            $meta->url = '/show/'.$KID.'-'.\Slug::make($show_name).'-'.$show_year;
            $meta->h1 = "{$show_name} {$show_year}";
            $meta->image = "catalog/show/{$KID}/".\Slug::make($show_name)."-".$show_year."-poster-path.jpg";

            try {
                $meta->save();
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            } 

            // return response()->json(Show::findOrFail($show->id));  
            // return redirect()->route('show', ['kinopoisk_id' => $KID]); 
        } else {
            return redirect()->route('show', ['kinopoisk_id' => $KID]);
        }
    }

    public function lastAdded() {
        $show = new Show();
        
        return Show::join('show_translators', 'shows.kinopoisk_id', 'show_translators.kinopoisk_id')->take(15)->groupBy('shows.kinopoisk_id')->orderBy('shows.id', 'desc')->get();
    }

    public function getById() {
        $show = new Show();
        $KID = $_GET['kinopoisk_id'];

        return Show::join('meta_data', 'shows.kinopoisk_id', 'meta_data.kinopoisk_id')->where('shows.kinopoisk_id', $KID)->get();
    }

    public function getTranslators(Request $request) {
        $KID = $request->input('kinopoisk_id');
        $TRANSLATOR = $request->input('translator_id');
        $moon = Help::getHttpRequest("http://moonwalk.cc/api/serial_episodes.json?kinopoisk_id={$KID}&translator_id={$TRANSLATOR}&api_token=".Help::MOON);
        // echo $KID;
        // echo $TRANSLATOR;
        // return response()->json($moon);
        // $arr = array();
        $arr = DB::table('show_translators')->where('kinopoisk_id', $KID)->where('translator_id', $TRANSLATOR)->get();

        // $arr = array_merge((array) $arr, $moon['season_episodes_count']);
        // for($i=0; $i < count($arr); $i++) {
        //     for($j=0; $j < count($moon); $j++) {
        //         if($arr[$i]['translator_id'] == $moon[$j]['serial']['translator_id']) {
        //             array_merge((array) $arr, $moon['season_episodes_count']);
        //         }
        //     }
        // }

        if(isset($TRANSLATOR)) {
            return DB::table('show_translators')->where('kinopoisk_id', $KID)->where('translator_id', $TRANSLATOR)->get();
        }
        return DB::table('show_translators')->where('kinopoisk_id', $KID)->get();
    }

    public function addTranslators() {
        $KID = $_GET['kinopoisk_id'];
        $help = new Help();

        $moon = Help::getHttpRequest('http://moonwalk.cc/api/videos.json?kinopoisk_id='.$KID.'&api_token='.Help::MOON); 

        $translators_array = array();   

        for($i=0; $i < count($moon); $i++) {

            if(DB::table('show_translators')
                ->where('translator_id', $moon[$i]['translator_id'])
                ->where('kinopoisk_id', $KID)
                ->get()
                == '[]'
                ) {
                array_push($translators_array, array(
                    'translator_id' => $moon[$i]['translator_id'],
                    'kinopoisk_id' => $moon[$i]['kinopoisk_id'],
                    'translator' => $moon[$i]['translator'],
                    'iframe_url' => $moon[$i]['iframe_url']
                ));
            }
        }

        if(count($translators_array) > 0) {
            
            try {
                DB::table('show_translators')->insert($translators_array);
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            }

            return DB::table('show_translators')->where('kinopoisk_id', $KID)->get();
        } else {
            return redirect()->route('show_translators', ['kinopoisk_id' => $KID]);
        }
         

    }

    static function ifNotNull($var) {
        return $var == null ? 0 : $var;
    }
}