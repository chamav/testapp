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

    //user list
    Route::get('users', 'Api\UserController@index');
    Route::get('user/{user_id}', 'Api\UserController@get');
    Route::delete('user/{user_id}',  'Api\UserController@destroy');

    Route::group(['prefix' => 'user'], function () {
        Route::post('registration', 'Api\UserController@registration');

        Route::group(['middleware' => 'jwt-auth'], function () {

            Route::put('/', 'Api\UserController@saveProfile');

        });


        Route::post('authorization',    'Api\UserController@login');

    });

    //Media entity
    Route::group(['prefix' => 'media'], function () {
        Route::group(['middleware' => 'jwt-auth'], function () {

            Route::post('uploadFile', 'Api\MediaController@store');

        });
    });




});

