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

//$router->get('/', function () use ($router) {
//    return $router->app->version();
//});

//$router->group(['middleware' => 'auth'], function () use ($router) {
//    $router->get('/test','TestController@test');
//});


$router->post('/requestToken','SmsTokenController@requestToken');
$router->post('/validateToken','SmsTokenController@validateToken');
$router->post('/register','AuthController@register');
$router->post('/login','AuthController@login');
$router->post('/requestResetPassToken','SmsTokenController@requestResetPassToken');
$router->post('/requestResetPassToken','SmsTokenController@requestResetPassToken');
$router->post('/resetPassword','AuthController@resetPassword');

//routes need auth middleware
$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/registerInvatition','InvitationalCodeController@registerInvatition');
    $router->post('/getInvatitionCode','InvitationalCodeController@getInvatitionCode');
});

