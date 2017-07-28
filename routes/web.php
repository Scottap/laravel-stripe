<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*Social*/
Route::get('/social/handle/{provider}', 'Auth\SocialController@getSocialHandle')->name('handle');
Route::get('/social/redirect/{provider}', 'Auth\SocialController@getSocialRedirect')->name('redirect');

/*Admin*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth:administrator'], function()
{
    Route::get('/', 'AdminController@getHome')->name('home');
});

/*Activation*/
Route::group(['prefix' => 'user', 'middleware' => 'auth:user'], function()
{
    Route::get('/', 'UserController@getHome')->name('home');

    Route::group(['middleware' => 'activated'], function ()
    {
        Route::get('protected', 'UserController@getProtected')->name('activated');
    });

});

/*Authenticated*/
Route::group(['middleware' => 'auth:all'], function()
{
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
    Route::get('/activate/{token}', 'ActivateController@activate')->name('activate');
    Route::get('/activate', 'ActivateController@resend')->name('activation-resend');
    Route::get('not-activated', ['as' => 'not-activated', 'uses' => function () {
        return view('errors.not-activated');
    }]);

});

Auth::routes(['login' => 'auth.login']);


