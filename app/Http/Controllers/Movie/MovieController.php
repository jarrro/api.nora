<?php
namespace App\Http\Controllers\Movie;

use App\Movie;
use App\Meta;

use App\Help;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManager;
use Illuminate\Routing\Redirector;

class MovieController extends Controller {

    public function create(Request $request, $id) {
        $help = new Help();
        $movie = new Movie();
        $meta = new Meta();

        $KID = $id;  


        if(Movie::where('kinopoisk_id', $KID)->get() == '[]') {    
            $moon = Help::getHttpRequest('http://moonwalk.cc/api/videos.json?kinopoisk_id='.$KID.'&api_token='.Help::MOON)[0]; 


            $query = $moon['title_en'] === null || '' ? urlencode($moon['title_ru']) : urlencode($moon['title_en']);
            $year = $moon['year'];

            $tmdb = Help::getHttpRequest('https://api.themoviedb.org/3/search/movie?api_key='.Help::TMDB.'&language=ru&query='.$query.'&year='.$year.'&page=1')['results'];            
            
            if( !empty($tmdb) ) {
                $tmdb = $tmdb[0];
            } else {
                $query = urlencode( $moon['title_en'] );
                $tmdb = Help::getHttpRequest('https://api.themoviedb.org/3/search/movie?api_key='.Help::TMDB.'&language=ru&query='.$query.'&year='.$year.'&page=1')['results'];
                
                if(empty($tmdb)) {
                    return response('Hello World', 200);
                }
            }

            /**Проверка на пользователя */
            if($request->cookie('user_id')) {
                $user = $request->cookie('user_id');
            } else {
                $user = 0;
            }           
            

            $structure = "catalog/movie/{$KID}/";
            $manager = new ImageManager(); 
            @mkdir($structure, 0777, true);  

                
            if(!file_exists("catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path.jpg") && $tmdb['backdrop_path'] != '') {
                
                $backdrop = $manager->make("https://image.tmdb.org/t/p/original".$tmdb['backdrop_path']);
                $backdrop->save("catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path.jpg");
                $backdrop->resize(300, null, function($constraint) {
                    $constraint->aspectRatio();
                });
                $backdrop->save("catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path-thumb.jpg");
            }

            if(!file_exists("catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path.jpg") && $tmdb['poster_path'] != '') {
                $poster = $manager->make("https://image.tmdb.org/t/p/original".$tmdb['poster_path']);
                $poster->save("catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path.jpg");
                $poster->resize(300, null, function($constraint) {
                    $constraint->aspectRatio();
                });
                $poster->save("catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path-thumb.jpg");              
            }  

            $movie->title_ru = $this::ifNotNull($moon['title_ru']);
            $movie->title_en = $this::ifNotNull($moon['title_en']);
            $movie->year = $this::ifNotNull($moon['year']);
            $movie->duration = $this::ifNotNull($moon['duration']['seconds']);

            $movie->kinopoisk_id = $KID;
            $movie->world_art_id = $this::ifNotNull($moon['world_art_id']);
            $movie->movie_db_id = $tmdb['id'];

            $movie->camrip = $this::ifNotNull($moon['camrip']);
            $movie->source_type = $this::ifNotNull($moon['source_type']);
            $movie->instream_ads = $this::ifNotNull($moon['instream_ads']);
            $movie->tagline = $this::ifNotNull($moon['material_data']['tagline']);
            $movie->description = $this::ifNotNull($moon['material_data']['description']);
            $movie->age_limit = $this::ifNotNull($moon['material_data']['age']);
            $movie->countries = $this::ifNotNull(json_encode($moon['material_data']['countries']));
            $movie->genries = $this::ifNotNull(json_encode($moon['material_data']['genres']));
            $movie->actors = $this::ifNotNull(json_encode($moon['material_data']['actors']));
            $movie->directors = $this::ifNotNull(json_encode($moon['material_data']['directors']));                      
            $movie->poster_path = "catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path.jpg";
            $movie->backdrop_path = "catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path.jpg";            
            $movie->poster_path_thumb = "catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path-thumb.jpg";
            $movie->backdrop_path_thumb = "catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-backdrop-path-thumb.jpg";
            // TRAILER
            $movie->studios = $this::ifNotNull(json_encode($moon['material_data']['studios']));    
            $movie->kinopoisk_rating = $this::ifNotNull($moon['material_data']['kinopoisk_rating']);
            $movie->kinopoisk_votes = $this::ifNotNull($moon['material_data']['kinopoisk_votes']);
            $movie->imdb_rating = $this::ifNotNull($moon['material_data']['imdb_rating']);
            $movie->imdb_votes = $this::ifNotNull($moon['material_data']['imdb_votes']);
            $movie->iframe_url = $moon['iframe_url'];  
            $movie->added_user = $user;
            
            

            try {
                $movie->save();
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            }    

            $movie_name = $moon['title_ru'];
            $movie_year = $moon['year'];

            $meta->id_material = $movie->id;
            $meta->kinopoisk_id = $KID;
            $meta->title = "{$movie_name} ({$movie_year}): Смотреть фильм бесплатно в качестве 720";
            $meta->description = strip_tags($moon['material_data']['description']);
            $meta->keywords = "фильм {$movie_name}, {$movie_name} {$movie_year}, {$movie_name} смотреть онлайн, {$movie_name} в хорошем качестве";
            $meta->url = '/movie/'.$KID.'-'.\Slug::make($movie_name).'-'.$movie_year;
            $meta->h1 = "{$movie_name} {$movie_year}";
            $meta->image = "catalog/movie/{$KID}/".\Slug::make(str_replace('/', '_', $moon['title_ru']))."-".$moon['year']."-poster-path.jpg";

            try {
                $meta->save();
            } catch (\Exception $e) {
                return response()->json(['created' => false, 'error' => $e->getMessage()]);
            } 

            return response()->json(Movie::join('meta_data', 'movies.kinopoisk_id', 'meta_data.kinopoisk_id')->where('movies.kinopoisk_id', $KID)->get()[0]);            
        } else {
            return redirect()->route("movie", ['id'=> $KID]);
        }
    }              

    public function getById($id) {
        $movie = new Movie();
        $KID = $id;//$_GET['kinopoisk_id'];

        return Movie::join('meta_data', 'movies.kinopoisk_id', 'meta_data.kinopoisk_id')->where('movies.kinopoisk_id', $KID)->get()[0];
    }

    public function lastAdded() {
        $movie = new Movie();
        
        $arr = Movie::take(15)->join('meta_data', 'movies.kinopoisk_id', 'meta_data.kinopoisk_id')->orderBy('movies.id', 'desc')->get();
    
        return (object)$arr;
    }


    public function update($id) {

    }

    public function delete($id) {

    }

    static function ifNotNull($var) {
        return $var == null ? 0 : $var;
    }



    

}