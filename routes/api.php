<?php

use Dingo\Api\Routing\Router;
use Illuminate\Http\Request;
use Specialtactics\L5Api\Http\Middleware\CheckUserRole;

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
    $api->get('/telemetry', 'App\Http\Controllers\TelemetryController@post');
    $api->post('/telemetry', 'App\Http\Controllers\TelemetryController@post');
});