<?php

use Illuminate\Http\Request;

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

Route::post('/register', 'Api\UserController@register');
Route::post('/registerInfo', 'Api\UserController@registerInfo');
Route::post('/login', 'Api\UserController@login');

Route::get('/login/{social}','Api\SocialAuthController@redirect')->where('social','twitter|facebook|linkedin|google|github|bitbucket');
Route::get('/login/{social}/callback','Api\SocialAuthController@callback')->where('social','twitter|facebook|linkedin|google|github|bitbucket');

Route::get('payPremium', ['as'=>'payPremium','uses'=>'Api\PaypalController@payPremium']);
Route::get('getCheckout', ['as'=>'getCheckout','uses'=>'Api\PaypalController@getCheckout']);
Route::get('getDone', ['as'=>'getDone','uses'=>'Api\PaypalController@getDone']);
Route::get('getCancel', ['as'=>'getCancel','uses'=>'Api\PaypalController@getCancel']);
Route::post('/payout', ['as'=>'payout','uses'=>'Api\PaypalController@payout']);

Route::group(['middleware' => ['jwt.auth']], function()
{
    Route::post('/addFriend', 'Api\FriendController@addFriend');
    Route::get('/getFriendList', 'Api\FriendController@getFriendList');

    Route::post('/sendMessage', 'Api\ChatController@sendMessage');
    Route::get('/loadMessages', 'Api\ChatController@loadMessages');

    Route::post('/getLeftMins', 'Api\UserController@getLeftMins');
    Route::post('/buyMins', 'Api\UserController@buyMins');
    Route::post('/pastOneMin', 'Api\UserController@pastOneMin');
    Route::post('/paidMin', 'Api\UserController@paidMin');

    Route::post('/upload/imageContent', 'Api\UploadController@imageContent');

});