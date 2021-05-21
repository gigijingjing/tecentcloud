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

Route::get('auth/captcha-src', 'Api\AuthController@getCaptchaSrc');
Route::get('auth/captcha-img', 'Api\AuthController@getCaptchaImg');
Route::post('auth/login', 'Api\AuthController@postLogin');
Route::post('auth/send_code', 'Api\AuthController@postSendCode');
Route::post('auth/register', 'Api\AuthController@postRegister');

Route::group(['middleware' => 'auth.api'], function() {
    // 用户
    Route::get('auth/user', 'Api\AuthController@getUser');
    // 产品
    Route::get('product/home', 'Api\GoodsController@getHome');
    Route::get('product/list', 'Api\GoodsController@getList');
    Route::get('product/{id}', 'Api\GoodsController@getDetail');
});
