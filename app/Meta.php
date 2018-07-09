<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model {
    public $table = 'meta_data';

    protected $fillable = [
         'id_material',
         'kinopoisk_id',
         'title',
         'description',
         'keywords',
         'url',
         'h1',
         'image'
    ];
}