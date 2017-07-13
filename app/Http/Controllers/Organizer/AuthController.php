<?php

namespace App\Http\Controllers\Organizer;

use App\Helpers\GuzzleHelper;
use App\Helpers\ValidatorHelper;
use App\Member;
use Cookie;
use DB;
use Hash;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $data = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'token_in_json' => $request->input('token_in_json') ?: 0
        ];
        $member_type = $this->getMemberType();

        if (!$validatorResponse = ValidatorHelper::validateLoginRequest($data)) {
            if ($member_id = Member::getMemberIDByEmail($data['email'], $member_type)) {
                $response = GuzzleHelper::requestAccessToken($member_id, $data['password']);
                if ($response['code'] == 200) {
                    if (!$data['token_in_json']) {
                        return response()
                            ->json(['code' => 200, 'message' => ['Login Success.']])
                            ->cookie('organizer_token', $response['access_token'], 0, '/', '', false, false);
                    } else {
                        $member = Member::find($member_id);
                        $member_json = [
                            'id' => $member->id,
                            'email' => $member->email,
                            'name' => $member->name,
                            'steam32_id' => $member->steam32_id,
                            'image' => $member->picture_file_name ? asset('storage/member/'.$participant->picture_file_name) : asset('img/default-profile.jpg');
                        ];

                        return response()->json(['code' => 200, 'message' => ['Login Success.'], 'token' => $response['access_token'], 'user' => $member_json]);
                    }
                } else {
                    return response()->json($response, 200);
                }
            } else {
                return response()->json(['code' => 400, 'message' => ['E-mail or Password is not valid.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function register(Request $request)
    {
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation')
        ];
        $member_type = $this->getMemberType();

        if (!$validatorResponse = ValidatorHelper::validateRegisterRequest($data, $member_type)) {
            DB::beginTransaction();
            try {
                $member = new Member([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'member_type' => $member_type,
                    'password' => Hash::make($data['password'])
                ]);
                $member->save();

                DB::commit();
                return response()->json(['code' => 201, 'message' => ['Register success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function logout(Request $request)
    {
        $accessTokenID = (new Parser)->parse($request->bearerToken())->getHeader('jti');
        DB::beginTransaction();
        try {
            DB::table('oauth_access_tokens')->where('id', $accessTokenID)->delete();
            DB::table('oauth_refresh_tokens')->where('access_token_id', $accessTokenID)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Logout fail.']]);
        }

        return response()->json(['code' => 200, 'message' => ['Logout success.']]);
    }

    public function webLogout(Request $request)
    {
        $accessTokenID = (new Parser)->parse($request->cookie('organizer_token'))->getHeader('jti');
        DB::beginTransaction();
        try {
            DB::table('oauth_access_tokens')->where('id', $accessTokenID)->delete();
            DB::table('oauth_refresh_tokens')->where('access_token_id', $accessTokenID)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        $cookie = Cookie::forget('organizer_token');
        // return redirect()->back()->withCookie($cookie);
        return redirect('/organizer')->withCookie($cookie);
    }
}
