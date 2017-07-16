<?php

namespace App\Helpers;

use App\Match;
use App\Team;
use App\Tournament;
use App\TournamentRegistration;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class GuzzleHelper
{
    public static function requestAccessToken($member_id, $password)
    {
        $http = new GuzzleClient();

        try {
            $response = $http->post('http://dota-battleground.local/oauth/token', [
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
            $response = $http->get('http://dota-battleground.local/api/user', [
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

    public static function destroyTournamentChallonge(Tournament $tournament)
    {
        $http = new GuzzleClient();

        try {
            if ($tournament->challonges_id) {
                $response = $http->delete('https://api.challonge.com/v1/tournaments/'.$tournament->challonges_id.'.json', [
                    'form_params' => [
                        'api_key' => env('CHALLONGE_API_KEY', '')
                    ]
                ]);

                return json_decode($response->getBody()->__toString(), false);
            } else {
                return true;
            }
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

    public static function destroyTournamentChallongeParticipant(TournamentRegistration $tournament_registration)
    {
        $http = new GuzzleClient();

        try {
            $tournament = $tournament_registration->tournament;
            if ($tournament->challonges_id) {
                if ($tournament_registration->challonges_participants_id) {
                    $response = $http->delete('https://api.challonge.com/v1/tournaments/'.$tournament->challonges_id.'/participants/'.$tournament_registration->challonges_participants_id.'.json', [
                        'form_params' => [
                            'api_key' => env('CHALLONGE_API_KEY', '')
                        ]
                    ]);

                    return json_decode($response->getBody()->__toString(), false);
                } else {
                    return true;
                }
            } else {
                return true;
            }
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

    public static function finalizeTournamentChallonge(Tournament $tournament)
    {
        $http = new GuzzleClient();

        try {
            $challonge_tournament_id = $tournament->challonges_id;

            $response = $http->post('https://api.challonge.com/v1/tournaments/'.$challonge_tournament_id.'/finalize.json', [
                'form_params' => [
                    'api_key' => env('CHALLONGE_API_KEY', '')
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function requestDota2LiveLeagueGames()
    {
        $http = new GuzzleClient();

        try {
            $response = $http->get('http://api.steampowered.com/IDOTA2Match_570/GetLiveLeagueGames/v1', [
                'query' => [
                    'key' => env('DOTA2_API_KEY', ''),
                    'format' => 'xml'
                ]
            ]);

            $xml_obj = simplexml_load_string($response->getBody()->__toString());
            $obj = json_decode(json_encode((array) $xml_obj), false);
            return $obj;
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function requestDota2MatchDetails($dota2_match_id)
    {
        $http = new GuzzleClient();

        try {
            $response = $http->get('http://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/v1', [
                'query' => [
                    'key' => env('DOTA2_API_KEY', ''),
                    'match_id' => $dota2_match_id
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }

    public static function updateTournamentMatchScore(Tournament $tournament, Match $match, $scores_csv, $winner_id)
    {
        $http = new GuzzleClient();

        try {
            $challonge_tournament_id = $tournament->challonges_id;
            $challonge_match_id = $match->challonges_match_id;

            $response = $http->put('https://api.challonge.com/v1/tournaments/'.$challonge_tournament_id.'/matches/'.$challonge_match_id.'.json', [
                'query' => [
                    'api_key' => env('CHALLONGE_API_KEY', ''),
                    'match' => [
                        'scores_csv' => $scores_csv,
                        'winner_id' => $winner_id
                    ]
                ]
            ]);

            return json_decode($response->getBody()->__toString(), false);
        } catch (RequestException $e) {
            return null;
        }
    }
}
