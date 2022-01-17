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

$router->post('/test','TestController@test');


$router->post('/requestToken','SmsTokenController@requestToken');
$router->post('/validateToken','SmsTokenController@validateToken');
$router->post('/register','AuthController@register');
$router->post('/login','AuthController@login');
$router->post('/requestResetPassToken','SmsTokenController@requestResetPassToken');
$router->post('/resetPassword','AuthController@resetPassword');

//routes need auth middleware
$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/registerInvatition','InvitationalCodeController@registerInvatition');
    $router->post('/getInvatitionCode','InvitationalCodeController@getInvatitionCode');
    $router->post('/updatePassword','UserController@updatePassword');
    $router->post('/updateProfile','UserController@updateProfile');
    $router->post('/uploadAvatar','UserAvatarController@uploadAvatar');
    $router->post('/deleteAvatar','UserAvatarController@deleteAvatar');
    $router->post('/addAddress','UserAddressController@addAddress');
    $router->post('/editAddress','UserAddressController@editAddress');
    $router->post('/getAddress','UserAddressController@getAddress');
    $router->post('/deleteAddress','UserAddressController@deleteAddress');
    $router->post('/getUserInfo','UserController@getUserInfo');

    $router->post('/getWalletData','WalletController@getWalletData');
    $router->post('/updateBankId','WalletController@updateBankId');
    $router->post('/requestIncreaseInventoryToken','SmsTokenController@requestIncreaseInventoryToken');
    $router->post('/increaseInventory','WalletController@increaseInventory');
    $router->post('/requestWithdrawalToken','SmsTokenController@requestWithdrawalToken');
    $router->post('/withdrawalRequest','WalletController@withdrawalRequest');
    $router->post('/historyOfRequests','WalletController@historyOfRequests');
    $router->post('/sendTicket','TicketController@sendTicket');
    $router->post('/mainTicketList','TicketController@mainTicketList');
    $router->post('/subTicketList','TicketController@subTicketList');


    $router->post('/getAllNotifications','NotificationController@getAllNotifications');
    $router->post('/getUserNotifications','UserNotificationController@getUserNotifications');
    $router->post('/getStoreData','StoreController@getStoreData');
    $router->post('/getBookData','BookController@getBookData');
    $router->post('/getStoreBooks','StoreController@getStoreBooks');
    $router->post('/addToFavList','UserFavoriteGoodController@addToFavList');
    $router->post('/deleteFromFavList','UserFavoriteGoodController@deleteFromFavList');
    $router->post('/getFavoriteList','UserFavoriteGoodController@getFavoriteList');
    $router->post('/addUserFavoriteData','UserFavoriteDataController@addUserFavoriteData');
    $router->post('/getUserFavoriteData','UserFavoriteDataController@getUserFavoriteData');
   // $router->post('/editUserFavoriteData','UserFavoriteDataController@editUserFavoriteData');

    $router->post('/searchByLocation','SearchController@searchByLocation');
    $router->post('/searchStoreBooks','SearchController@searchStoreBooks');
    $router->post('/searchCategory','SearchController@searchCategory');
    $router->post('/searchHashtag','SearchController@searchHashtag');
    $router->post('/search','SearchController@search');
    $router->post('/homeAll','HomeController@homeAll');
    $router->post('/homeBook','HomeController@homeBook');
    $router->post('/frequentSearches','SearchController@frequentSearches');

    $router->post('/sendComment','CommentController@sendComment');

});
$router->get('/verifyIncreaseInventory/{amount}/{userId}','WalletController@verifyIncreaseInventory');

