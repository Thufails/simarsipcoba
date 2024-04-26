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

$router->group(['prefix' => 'arsipkematian'], function () use ($router) {
    $router->post('/simpanKematian', 'InfoArsipKematianController@simpanKematian');
    $router->post('/updateKematian/{ID_ARSIP}', 'InfoArsipKematianController@updateKematian');
});

$router->group(['prefix' => 'arsipktp'], function () use ($router) {
    $router->post('/simpanKtp', 'InfoArsipKtpController@simpanKtp');
    $router->post('/updateKtp/{ID_ARSIP}', 'InfoArsipKtpController@updateKtp');
});

$router->group(['prefix' => 'arsipkk'], function () use ($router) {
    $router->post('/simpanKtp', 'InfoArsipKtpController@simpanKtp');
    $router->post('/updateKtp/{ID_ARSIP}', 'InfoArsipKtpController@updateKtp');
});

$router->group(['prefix' => 'arsippengangkatan'], function () use ($router) {
    $router->post('/simpanPengangkatan', 'InfoArsipPengangkatanController@simpanPengangkatan');
    $router->post('/updatePengangkatan/{ID_ARSIP}', 'InfoArsipPengangkatanController@updatePengangkatan');
});

$router->group(['prefix' => 'arsippengesahan'], function () use ($router) {
    $router->post('/simpanPengesahan', 'InfoArsipPengesahanController@simpanPengesahan');
    $router->post('/updatePengesahan/{ID_ARSIP}', 'InfoArsipPengesahanController@updatePengesahan');
});

$router->group(['prefix' => 'arsippengakuan'], function () use ($router) {
    $router->post('/simpanPengakuan', 'InfoArsipPengakuanController@simpanPengakuan');
    $router->post('/updatePengakuan/{ID_ARSIP}', 'InfoArsipPengakuanController@updatePengakuan');
});

$router->group(['prefix' => 'arsipperkawinan'], function () use ($router) {
    $router->post('/simpanPerkawinan', 'InfoArsipPerkawinanController@simpanPerkawinan');
    $router->post('/updatePerkawinan/{ID_ARSIP}', 'InfoArsipPerkawinanController@updatePerkawinan');
});

$router->group(['prefix' => 'arsipperceraian'], function () use ($router) {
    $router->post('/simpanPerceraian', 'InfoArsipPerceraianController@simpanPerceraian');
    $router->post('/updatePerceraian/{ID_ARSIP}', 'InfoArsipPerceraianController@updatePerceraian');
});
