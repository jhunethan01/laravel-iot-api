<?php

use Dingo\Api\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
 * Welcome route - link to any public API documentation here
 */
Route::get('/', function () {
    echo 'Welcome to our API';
});

/** @var \Dingo\Api\Routing\Router $api */

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function (Router $api) {
    $api->get('/alerts', 'App\Http\Controllers\AlertController@index');

    $api->post('/devices', 'App\Http\Controllers\DeviceController@post');
    $api->get('/devices', 'App\Http\Controllers\DeviceController@index');
    $api->get('/devices/{id}', 'App\Http\Controllers\DeviceController@show');

    $api->post('/telemetry', 'App\Http\Controllers\TelemetryController@post');
});