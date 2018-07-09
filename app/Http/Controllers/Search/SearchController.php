<?php
namespace App\Http\Controllers\Search;

use App\Movie;
use App\Show;

use App\Help;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller {
    public function get() {

        $TITLE = Input::get('title');
        $TYPE = Input::get('type');

        $movie = new Movie();
        $show = new Show();

        $moon = Help::getHttpRequest('http://moonwalk.cc/api/videos.json?title='.urlencode($TITLE).'&api_token='.Help::MOON);

        $search_movie = $movie::join('meta_data', 'movies.kinopoisk_id', 'meta_data.kinopoisk_id')->where('movies.name_ru', 'like', '%'.$TITLE.'%')->orWhere('movies.name_en', 'like', '%'.$TITLE.'%')->get()->toArray();
        $search_show = $show::join('meta_data', 'shows.kinopoisk_id', 'meta_data.kinopoisk_id')->where('shows.name_ru', 'like', '%'.$TITLE.'%')->orWhere('shows.name_en', 'like', '%'.$TITLE.'%')->get()->toArray();

        $search_result = array();

        // if($TYPE == 'movie') {
            foreach($search_movie as $search_movie__value) {
                array_push($search_result, $search_movie__value);
            }
        // }

        // if($TYPE == 'serial') {
            foreach($search_show as $search_show__value) {
                array_push($search_result, $search_show__value);
            }
        // }

        foreach($moon as $moon__value) {
            array_push($search_result, $moon__value);
        }

        function unique_multidim_array($array, $key) { 
            $temp_array = array(); 
            $i = 0; 
            $key_array = array(); 
            
            foreach($array as $val) { 
                if (!in_array($val[$key], $key_array)) { 
                    $key_array[$i] = $val[$key]; 
                    $temp_array[$i] = $val; 
                } 
                $i++; 
            } 
            return $temp_array; 
        } 

        return unique_multidim_array($search_result, 'kinopoisk_id');
    }
}