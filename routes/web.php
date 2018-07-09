<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->middleware([
    // ...
    \Barryvdh\Cors\HandleCors::class,
]);

/**
* Movie
**/

$app->group(['prefix' => 'v1', 'namespace' => 'Movie'], function() use ($app) {
    $app->get('/movie/last_added', 'MovieController@lastAdded');
    $app->get('/movie/{id}', [
        'as' => 'movie',
        'uses' => 'MovieController@getById'
    ]);    
    $app->post('/movie/{id}', [
        'as' => 'movieAdd',
        'uses' => 'MovieController@create'
    ]);
    $app->put('/movie/{id}', 'MovieController@update');
    $app->delete('/movie/{id}', 'MovieController@delete');
});

/**
* Show
**/

$app->group(['prefix' => 'v1', 'namespace' => 'Show'], function() use ($app) {
    $app->get('/show/last_added', 'ShowController@lastAdded');
    $app->get('/show', [
        'as' => 'show',
        'uses' => 'ShowController@getById'
    ]);

    $app->get('/show/translators', [
        'as' => 'show_translators',
        'uses' => 'ShowController@getTranslators'
    ]);
    $app->post('/show/translators', 'ShowController@addTranslators');
    
    

    $app->post('/show/episode', 'EpisodeController@create');
    
    $app->get('/show/episode', [
        'as' => 'episode',
        'uses' => 'EpisodeController@getEpisode'
    ]);
    
    $app->get('/show/season_episode_count', 'EpisodeController@getEpidesCount');
    $app->get('/show/episode/last_added', 'EpisodeController@lastAdded');
    
    $app->get('/show/season', 'SeasonController@get');
    $app->post('/show/season', 'SeasonController@create');
    $app->post('/show/{id}', 'ShowController@create');
});

/**
* Person
**/

$app->group(['prefix' => 'v1', 'namespace' => 'Person'], function() use ($app) {
    $app->post('/person', 'PersonController@create');
    $app->get('/person/{id}', 'PersonController@getById');
    $app->get('/person/project/{id}', 'PersonController@getByProjectId');
}); 

/**
* Search
**/

$app->group(['prefix' => 'v1', 'namespace' => 'Search'], function() use ($app) {
    $app->get('/search', 'SearchController@get');
});

$app->get('/', function () use ($app) {
    return $app->version();
});

// $app->get('/movie/{id}', function($id) use ($app) {
//     return response()->json(['name' => $id]);
//     // return response()->json(['name' => 'Abigail', 'state' => 'CA']);
//     // return 'Hello '.$id;
// });