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
        Route::get('/team/{id}/member', 'TeamController@member');
        Route::post('/tournament/{id}/register', 'TournamentController@register');
        Route::post('/tournament/confirm-payment/{id}', 'TournamentController@confirmPayment');
        Route::post('/dota-2/match/{id}/comment', 'Dota2MatchController@postComment');
    });
});

Route::group(['prefix' => 'organizer', 'namespace' => 'Organizer'], function() {
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::put('/password', 'ProfileController@updatePassword');
        Route::post('/tournament/create', 'TournamentController@store');
        Route::put('/tournament/{id}', 'TournamentController@update');
        Route::put('/tournament/{id}/type', 'TournamentController@updateType');
        Route::put('/tournament/{id}/start', 'TournamentController@start');
        Route::put('/tournament/{id}/end', 'TournamentController@end');
        Route::put('/match/{id}/schedule', 'MatchController@updateSchedule');
        Route::put('/match/{id}/score', 'MatchController@updateScore');
    });
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function() {
    Route::post('/login', 'AuthController@login');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::post('/tournament/{id}/approve', 'TournamentController@approve');
        Route::post('/tournament/{id}/decline', 'TournamentController@decline');
        Route::post('/tournament-payment/{id}/approve', 'TournamentController@approvePayment');
        Route::post('/tournament-payment/{id}/decline', 'TournamentController@declinePayment');

        Route::put('/dota-2/abilities', 'Dota2Controller@updateAbilities');
        Route::put('/dota-2/heroes', 'Dota2Controller@updateHeroes');
        Route::put('/dota-2/items', 'Dota2Controller@updateItems');
    });
});

Route::get('/user', function (Request $request) {
    return response()->json($request->user());
})->middleware('auth:api');
