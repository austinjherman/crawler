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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/users/create', ['uses' => 'UserController@create']);
$router->get('/users/{hash}', ['middleware' => 'auth', 'uses' => 'UserController@read']);
$router->post('/users/update/{hash}', ['middleware' => 'auth', 'uses' => 'UserController@update']);
$router->post('/users/delete/{hash}', ['middleware' => 'auth', 'uses' => 'UserController@delete']);

// TODO
// set expiry on tokens
$router->post('/users/authenticate', ['uses' => 'AuthController@login']);
$router->get('/protected', ['middleware' =>'auth', 'uses' => 'UserController@test']);

$router->get('/crawl/urls', ['middleware' =>'auth', 'uses' => 'UrlCrawlerController@run']);

