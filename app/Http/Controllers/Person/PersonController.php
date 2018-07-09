<?php
namespace App\Http\Controllers\Person;

use App\Person;
use App\PersonToProject;

use App\Help;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManager;

class PersonController extends Controller {

    public function create() {
        $persons_array = array();
        $person_to_project = array();
        $person = new Person();        
        $personToProject = new PersonToProject();
        $KID = $_GET['kinopoisk_id'];
        $getmovie = Help::getHttpRequest("https://getmovie.cc/api/kinopoisk.json?id={$KID}&token=".Help::GETM);
        
        while($p = current($getmovie['creators'])) {
            for($i = 0; $i < count($p); $i++) {
                
                if(Person::where('kid_person', $p[$i]['kp_id_person'])->get() == '[]') {             
    
                    array_push($persons_array, array(
                        'id_project' => $KID,
                        'name_person_ru' => $p[$i]['name_person_ru'],
                        'name_person_en' => $p[$i]['name_person_en'],
                        'role' => key($getmovie['creators']),
                        'kid_person' => $p[$i]['kp_id_person'],
                        'photo_person' => $p[$i]['photos_person']
                    ));
                }

                if(PersonToProject::where('person_id', $p[$i]['kp_id_person'])->where('project_id', $KID)->get() == '[]') {
                    array_push($person_to_project, array(
                        'project_id' => $KID,
                        'person_id' => $p[$i]['kp_id_person']
                    ));
                } 
            }  

            next($getmovie['creators']);
        }
    
        try {
            Person::insert($persons_array);
        } catch (\Exception $e) {
            return response()->json(['created' => false, 'error' => $e->getMessage()]);
        } 

        try {
            PersonToProject::insert($person_to_project);
        } catch (\Exception $e) {
            return response()->json(['created' => false, 'error' => $e->getMessage()]);
        } 
    }

    public function getById($id) {
        $person = new Person();

        return Person::where('kid_person', $id)->get();
    }

    public function getByProjectId($id) {
        $person = new Person();

        return Person::where('id_project', $id)->get();
    }

}