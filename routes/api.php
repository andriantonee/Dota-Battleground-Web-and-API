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
    Route::get('/tournament', 'TournamentController@index');
    Route::get('/tournament/{id}/detail', 'TournamentController@getTournamentDetail');
    Route::get('/team', 'TeamController@index');
    Route::get('/team/{id}', 'TeamController@show');
    Route::get('/team/search', 'TeamController@searchTeam');
    Route::get('/dota-2/match/{id}', 'Dota2MatchController@showAPI');
    Route::get('/dota-2/match/{id}/comment', 'Dota2MatchController@getComment');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::get('/my-notification', 'ProfileController@getMyNotification');
        Route::post('/my-notification/{id}', 'ProfileController@postNotification');
        Route::get('/profile', 'ProfileController@getProfile');
        Route::get('/my-schedule', 'ProfileController@getMySchedule');
        Route::put('/profile', 'ProfileController@update');
        Route::put('/password', 'ProfileController@updatePassword');
        Route::post('/profile-picture', 'ProfileController@updateProfilePicture');
        Route::delete('/profile-picture', 'ProfileController@deleteProfilePicture');
        Route::get('/my-identification', 'ProfileController@getMyIdentification');
        Route::post('/identification', 'ProfileController@updateIdentification');
        Route::get('/my-tournament', 'ProfileController@getMyTournament');
        Route::get('/my-register', 'ProfileController@getMyRegister');
        Route::get('/my-team', 'TeamController@getMyTeam');
        Route::post('/team', 'TeamController@store');
        Route::get('/my-team/{id}', 'TeamController@getMyTeamDetail');
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
        Route::get('/tournament/{id}/register', 'TournamentController@registerAPIIndex');
        Route::post('/tournament/{id}/register', 'TournamentController@register');
        Route::get('/tournament/confirm-payment/{id}', 'TournamentController@confirmPaymentAPIIndex');
        Route::post('/tournament/confirm-payment/{id}', 'TournamentController@confirmPayment');
        Route::post('/dota-2/match/{id}/comment', 'Dota2MatchController@postComment');
        Route::post('/logout', 'AuthController@logout');
    });
});

Route::group(['prefix' => 'organizer', 'namespace' => 'Organizer'], function() {
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::put('/password', 'ProfileController@updatePassword');
        Route::get('/my-tournament', 'TournamentController@getMyTournament');
        Route::get('/my-tournament/{id}', 'TournamentController@getMyTournamentDetail');
        Route::post('/tournament/create', 'TournamentController@store');
        Route::put('/tournament/{id}', 'TournamentController@update');
        Route::put('/tournament/{id}/type', 'TournamentController@updateType');
        Route::put('/tournament/{id}/start', 'TournamentController@start');
        Route::put('/tournament/{id}/end', 'TournamentController@end');
        Route::put('/tournament/{id}/finalize', 'TournamentController@finalize');
        Route::get('/match/{id}/schedule', 'MatchController@getSchedule');
        Route::put('/match/{id}/schedule', 'MatchController@updateSchedule');
        Route::put('/match/{id}/score', 'MatchController@updateScore');
        Route::get('/match/{id}/team-attendance', 'MatchController@getMatchTeamAttendance');
        Route::get('/match/{id}/attendance', 'MatchController@getAttendance');
        Route::post('/match/{id}/attendance', 'MatchController@postAttendance');
        // Route::post('/match/{id}/start', 'MatchController@start');
        Route::post('/dota-2/match/{id}/comment', 'Dota2MatchController@postComment');
        Route::post('logout', 'AuthController@logout');
    });
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function() {
    Route::post('/login', 'AuthController@login');

    Route::group(['middleware' => ['auth:api']], function() {
        Route::post('/tournament/{id}/update', 'TournamentController@update');
        Route::post('/tournament/{id}/approve', 'TournamentController@approve');
        Route::post('/tournament/{id}/decline', 'TournamentController@decline');
        Route::post('/tournament/{id}/undo', 'TournamentController@undo');
        Route::post('/tournament-payment/{id}/approve', 'TournamentController@approvePayment');
        Route::post('/tournament-payment/{id}/decline', 'TournamentController@declinePayment');
        Route::post('/tournament-payment/{id}/undo', 'TournamentController@undoPayment');

        Route::put('/dota-2/abilities', 'Dota2Controller@updateAbilities');
        Route::put('/dota-2/heroes', 'Dota2Controller@updateHeroes');
        Route::put('/dota-2/items', 'Dota2Controller@updateItems');
    });
});

Route::get('/user', function (Request $request) {
    return response()->json($request->user());
})->middleware('auth:api');
