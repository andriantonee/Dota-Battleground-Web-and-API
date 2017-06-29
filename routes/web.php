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

Route::group(['middleware' => ['exchange:participant,1', 'notification'], 'namespace' => 'Participant'], function() {
    Route::get('/', 'HomeController@index');
    Route::get('/tournament', 'TournamentController@index');
    Route::get('/tournament/{id}', 'TournamentController@show');
    Route::get('/team', 'TeamController@index');
    Route::get('/team/{id}', 'TeamController@show');
    Route::get('/dota-2/match/{id}', 'Dota2MatchController@show');

    Route::group(['middleware' => ['authorize:participant']], function() {
        Route::get('/profile', 'ProfileController@index');
        Route::post('/logout', 'AuthController@webLogout');

        Route::get('/tournament/{id}/register', 'TournamentController@registerIndex');
        Route::get('/tournament/confirm-payment/{id}', 'TournamentController@confirmPaymentIndex');
    });
});

Route::group(['prefix' => 'organizer', 'middleware' => ['exchange:organizer,2'], 'namespace' => 'Organizer'], function() {
    Route::group(['middleware' => ['already_authorize:organizer']], function() {
        Route::get('/', 'HomeController@index');
    });

    Route::group(['middleware' => ['authorize:organizer']], function() {
        Route::get('/dashboard', 'HomeController@dashboard');
        Route::get('/tournament', 'TournamentController@index');
        Route::get('/tournament/create', 'TournamentController@create');
        Route::get('/tournament/{id}/detail', 'TournamentController@detail');
        Route::get('/password', 'HomeController@password');
        Route::post('/logout', 'AuthController@webLogout');
        Route::get('/dota-2/match/{id}', 'Dota2MatchController@show');
    });
});

Route::group(['prefix' => 'admin', 'middleware' => ['exchange:admin,3'], 'namespace' => 'Admin'], function() {
    Route::group(['middleware' => ['already_authorize:admin']], function() {
        Route::get('/login', 'AuthController@index');
    });

    Route::group(['middleware' => ['authorize:admin']], function() {
        Route::get('/', 'HomeController@index');
        Route::get('/verify-tournament/{id}', 'TournamentController@detail');
        Route::get('/verify-tournament-payment', 'TournamentController@verifyTournamentPaymentIndex');
        Route::post('/logout', 'AuthController@webLogout');
    });
});

Route::get('/test', 'Admin\Dota2Controller@test');
