<?php

//use Illuminate\Http\Request;


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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api\V1',
    'middleware' => [
        'cors'
    ],
],function ($api){

    $api->post('token/check', 'ToolsController@tokenCheck');
    $api->post('upload','ToolsController@upload');
    $api->post('uploadvideo','ToolsController@uploadvideo');
    $api->post('send/sms','ToolsController@sendSms');

    $api->post('verification/sms','ToolsController@checkSmsCodeForApi');

});