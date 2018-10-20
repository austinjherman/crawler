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

$router->post('/register', ['uses' => 'UserController@create']);
$router->get('/users/{id}', ['middleware' => 'auth', 'uses' => 'UserController@read']);
$router->post('/users/update/{id}', ['middleware' => 'auth', 'uses' => 'UserController@update']);
$router->post('/users/delete/{id}', ['middleware' => 'auth', 'uses' => 'UserController@delete']);

$router->post('/login', ['uses' => 'AuthController@login']);

$router->get('/crawl/urls', ['middleware' =>'auth', 'uses' => 'UrlCrawlerController@run']);
