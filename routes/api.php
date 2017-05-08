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
    Route::get('/team/search', 'TeamController@searchTeam');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::get('/profile', 'ProfileController@show');
        Route::put('/profile', 'ProfileController@update');
        Route::put('/password', 'ProfileController@updatePassword');
        Route::post('/profile-picture', 'ProfileController@updateProfilePicture');
        Route::delete('/profile-picture', 'ProfileController@deleteProfilePicture');
        Route::post('/identification', 'ProfileController@updateIdentification');
        Route::post('/team', 'TeamController@store');
        Route::put('/team/{id}', 'TeamController@update');
        Route::post('/team/{id}/picture', 'TeamController@updatePicture');
        Route::delete('/team/{id}/picture', 'TeamController@deletePicture');
        Route::post('/team/{id}/join', 'TeamController@join');
        Route::get('/team/{id}/uninvited-member', 'TeamController@uninvitedMember');
        Route::put('/team/{id}/invite-member/{member_id}', 'TeamController@inviteMember');
        Route::post('/team/{id}/accept-invitation', 'TeamController@acceptInvitation');
        Route::post('/team/{id}/reject-invitation', 'TeamController@rejectInvitation');
        Route::delete('/team/{id}/kick-member/{member_id}', 'TeamController@kickMember');
    });
});

Route::group(['prefix' => 'organizer', 'namespace' => 'Organizer'], function() {
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::put('/password', 'ProfileController@updatePassword');
    });
});

Route::get('/user', function (Request $request) {
    return response()->json($request->user());
})->middleware('auth:api');
