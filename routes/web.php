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

$router->post('/users/authenticate', ['uses' => 'AuthController@login']);
$router->post('/users/create', ['uses' => 'UserController@create']);
$router->get('/users/{hash}', ['middleware' => 'auth', 'uses' => 'UserController@read']);
$router->post('/users/update/{hash}', ['middleware' => 'auth', 'uses' => 'UserController@update']);
$router->post('/users/delete/{hash}', ['middleware' => 'auth', 'uses' => 'UserController@delete']);

$router->get('/protected', ['middleware' =>'auth', 'uses' => 'UserController@test']);

$router->get('/crawl/urls', ['middleware' =>'auth', 'uses' => 'UrlCrawlerController@run']);

// TODO

// Should find all the links within a given domain and cache the responses
//$router->get('/crawl', ['middleware' => 'auth', 'uses' => '']);

// Should return a list of all sites that were crawled by the authenticated user
//$router->get('/sites', ['middleware' =>'auth', 'uses' => '']);

// Should return a summary of the requested domain, which should have been 
// crawled already by the authenticated user
//$router->get('/sites/{domain}', ['middleware' =>'auth', 'uses' => '']);

// Should return a json-formatted list of pages for wordpress import
//$router->get('/sites/{domain}/scrape', ['middleware' =>'auth', 'uses' => '']);

