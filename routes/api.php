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
Route::group(['middleware' => ['api'],'prefix' => 'v1/'], function () {
    //User entity


    Route::group(['prefix' => 'user'], function () {
        Route::post('registration', 'Api\UserController@registration');

        Route::post('authorization',    'APIUserController@login');

    });

    //Media entity
    Route::group(['prefix' => 'media'], function () {
        Route::group(['middleware' => 'jwt-auth'], function () {

            Route::post('uploadFile', 'Api\MediaController@store');

        });
    });




});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
