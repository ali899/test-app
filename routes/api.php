<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::group(["namespace" => "Api",], function () {
    Route::group(["prefix" => "auth",], function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('getUser', 'AuthController@getUser');
            Route::post('/logout', 'AuthController@logout');
            Route::post('/logoutAll', 'AuthController@logoutFromAllDevices');
        });
    });
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('totalImages', 'AdsController@totalImages');
        Route::get('adsCounters', 'AdsController@adsCounters');
        Route::get('imagesStatusCounters', 'AdsController@imagesStatusCounters');
        Route::post('setImageStatus', 'AdsController@setImageStatus');
        Route::Delete('removeImage/{imageName}', 'AdsController@removeImage');
        Route::Delete('removeAds/{id}', 'AdsController@removeAds');
        Route::get('export', 'AdsController@export');
        Route::get('getSignedImage/{imagePath}', 'AdsController@getSignedImageUrl');
        Route::get('remainingImages', 'AdsController@remainingImages');
    });
});