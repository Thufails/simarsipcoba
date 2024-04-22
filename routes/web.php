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
    $router->get('/filter', 'DashboardController@pencarian');
    $router->get('/getAllArsip', 'DashboardController@getAllArsip');
    $router->get('/getArsipDokumen/{ID_ARSIP}', 'DashboardController@getArsipDokumenById');
});

$router->group(['prefix' => 'profile', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/showUser', 'AuthController@showUser');
    //$router->get('/updateUser', 'AuthController@showUser');
});
$router->group(['prefix' => 'admins', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/showAdmin', 'AdminController@showAllAdmin');
    $router->get('/showAdmin/{id}', 'AdminController@showAdminById');
    $router->post('/addAdmin', 'AdminController@addAdmin');
    $router->post('/updateAdmin/{id}', 'AdminController@updateAdmin');
    $router->delete('/deleteAdmin/{id}', 'AdminController@deleteAdmin');
});

$router->group(['prefix' => 'surat', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/uploadSurat', 'SuratController@uploadSurat');
    $router->get('/showSurat', 'SuratController@showAllSurat');
    $router->get('/searchSurat', 'SuratController@searchSurat');
    $router->post('/updateSurat/{id}', 'SuratController@updateSurat');
    $router->delete('/deleteSurat/{id}', 'SuratController@deleteSurat');
});

$router->group(['prefix' => 'dokumen'], function () use ($router) {
    $router->post('/uploadDokumen', 'DokumenController@uploadDokumen');
    $router->get('/showDokumen', 'DokumenController@showAllDokumen');
    $router->get('/showDokumen/{id}', 'DokumenController@showDokumenById');
    $router->post('/updateDokumen/{id}', 'DokumenController@updateDokumen');
});

$router->post('/addHakAkses', 'HakAksesController@AddHakAkses');
