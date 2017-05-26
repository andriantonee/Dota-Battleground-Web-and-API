<?php

namespace App\Helpers;

use App\Team;
use App\Tournament;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class GuzzleHelper
{
    public static function requestAccessToken($member_id, $password)
    {
        $http = new GuzzleClient();

        try {
            $response = $http->post(url('/').'/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => env('PASSPORT_CLIENT_ID', ''),
                    'client_secret' => env('PASSPORT_CLIENT_SECRET', ''),
                    'username' => $member_id,
                    'password' => $password,
                    'scope' => ''
                ]
            ]);

            return array_merge(['code' => 200], json_decode($response->getBody()->__toString(), true));
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                if ($e->getResponse()->getStatusCode() == 401) {
                    return ['code' => 400, 'message' => ['E-mail or Password is not valid.']];
                } else {
                    return ['code' => 500, 'message' => [$e->getResponse()->getBody()->__toString()]];
                }
            } else {
                return ['code' => 500, 'message' => ['Something went wrong. Please try again.']];
            }
        }
    }

    public static function requestUserModel($access_token)
    {
        $http = new GuzzleClient();

        try {
            $response = $http->get(url('/').'/api/user', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$access_token
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function createTournamentChallonge(Tournament $tournament)
    {
        $http = new GuzzleClient();

        try {
            $name = substr('[Dota Battleground] ' . $tournament->name, 0, 60);
            $tournament_type = $tournament->type == 2 ? 'double elimination' : ($tournament->type == 1 ? 'single elimination' : 'single elimination');
            $url = 'dotabattleground_'.$tournament->id.md5(uniqid(rand(), true));

            $response = $http->post('https://api.challonge.com/v1/tournaments.json', [
                'form_params' => [
                    'api_key' => env('CHALLONGE_API_KEY', ''),
                    'tournament' => [
                        'name' => $name,
                        'tournament_type' => $tournament_type,
                        'url' => $url,
                        'open_signup' => false,
                        'hold_third_place_match' => true,
                        'ranked_by' => 'match wins',
                        'hide_forum' => true,
                        'show_rounds' => true,
                        'private' => true,
                        'grand_finals_modifier' => 'single match'
                    ]
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function createTournamentChallongeParticipant(Tournament $tournament, Team $team)
    {
        $http = new GuzzleClient();

        try {
            $name = $team->name;
            $challonge_tournament_id = $tournament->challonges_id;

            $response = $http->post('https://api.challonge.com/v1/tournaments/'.$challonge_tournament_id.'/participants.json', [
                'form_params' => [
                    'api_key' => env('CHALLONGE_API_KEY', ''),
                    'participant' => [
                        'name' => $name
                    ]
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function updateTournamentChallongeType(Tournament $tournament, $type)
    {
        $http = new GuzzleClient();

        try {
            $challonge_tournament_id = $tournament->challonges_id;
            $tournament_type = $type == 2 ? 'double elimination' : ($type == 1 ? 'single elimination' : 'single elimination');

            $response = $http->put('https://api.challonge.com/v1/tournaments/'.$challonge_tournament_id.'.json', [
                'form_params' => [
                    'api_key' => env('CHALLONGE_API_KEY', ''),
                    'tournament' => [
                        'tournament_type' => $tournament_type
                    ]
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function updateTournamentChallongeParticipantSeed(Tournament $tournament)
    {
        $http = new GuzzleClient();

        try {
            $challonge_tournament_id = $tournament->challonges_id;

            $response = $http->post('https://api.challonge.com/v1/tournaments/'.$challonge_tournament_id.'/participants/randomize.json', [
                'form_params' => [
                    'api_key' => env('CHALLONGE_API_KEY', '')
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function startTournamentChallonge(Tournament $tournament)
    {
        $http = new GuzzleClient();

        try {
            $challonge_tournament_id = $tournament->challonges_id;

            $response = $http->post('https://api.challonge.com/v1/tournaments/'.$challonge_tournament_id.'/start.json', [
                'form_params' => [
                    'api_key' => env('CHALLONGE_API_KEY', ''),
                    'include_matches' => 1
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }
}
