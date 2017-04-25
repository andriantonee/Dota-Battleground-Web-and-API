<?php

namespace App\Helpers;

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
}
