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

Route::group(['prefix' => 'participant', 'namespace' => 'Participant'], function() {
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::get('/profile', 'ProfileController@show');
        Route::put('/profile', 'ProfileController@update');
        Route::post('/profile-picture', 'ProfileController@updateProfilePicture');
        Route::delete('/profile-picture', 'ProfileController@deleteProfilePicture');
        Route::post('/identification', 'ProfileController@updateIdentification');
    });
});
Route::get('/user', function (Request $request) {
    return response()->json($request->user());
})->middleware('auth:api');
