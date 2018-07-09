<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Person extends Model {
    public $table = 'persons';
    
    protected $fillable = [
        'id_project',
        'name_person_ru',
        'name_person_en',
        'role',
        'kid_person',
        'photo_person'
    ];
}