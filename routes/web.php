<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
});

$router->group(['prefix' => 'dashboard'], function () use ($router) {
    $router->get('/index', 'DashboardController@pencarian');
});

$router->group(['prefix' => 'pencarian'], function () use ($router) {
    $router->get('/filter', 'PencarianController@pencarianFilter');
    $router->get('/getAllArsip', 'PencarianController@getAllArsip');
    $router->get('/getArsipById/{ID_ARSIP}', 'PencarianController@getArsipById');
    $router->get('/getArsipDokumen/{ID_ARSIP}', 'PencarianController@getArsipDokumenById');
});

$router->group(['prefix' => 'arsipkelahiran'], function () use ($router) {
    $router->post('/simpanKelahiran', 'InfoArsipKelahiranController@simpanKelahiran');
    $router->post('/updateKelahiran/{ID_ARSIP}', 'InfoArsipKelahiranController@updateKelahiran');
});
