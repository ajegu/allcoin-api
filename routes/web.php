<?php

/** @var Router $router */

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

use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/**
 * ASSET
 */
$router->post('/asset', ['uses' => 'AssetController@create']);
$router->get('/asset', ['uses' => 'AssetController@list']);
$router->put('/asset/{id}', ['uses' => 'AssetController@update']);
$router->delete('/asset/{id}', ['uses' => 'AssetController@delete']);
$router->get('/asset/{id}', ['uses' => 'AssetController@get']);
