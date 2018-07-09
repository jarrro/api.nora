<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonToProject extends Model {
    public $table = 'project_to_person';
    
    protected $fillable = [
        'project_id',
        'person_id'
    ];
}