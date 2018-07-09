<?php

namespace App;


class Help {

    const MOON = '169b12e92c8a8ea0c2828ccb4ce18847';
    const TMDB = '424827d007283e0e9a3ee5f48534878e';
    const TINY = 'i1qHq17FF7wumMUz4B7wBXtsNiOfrK7Y';
    const GETM = 'e361bab80ed430031237a3acb52677da';

    static function getHttpRequest($url) {
        $json = @file_get_contents($url, false);
        $json = json_decode($json, true);
        return $json;
    }       
    
}