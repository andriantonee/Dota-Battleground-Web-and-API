<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\GuzzleHelper;
use App\Helpers\ValidatorHelper;
use App\Member;
use Cookie;
use DB;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;

class AuthController extends BaseController
{
    public function index()
    {
        return view('admin.login');
    }

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
                            ->cookie('admin_token', $response['access_token'], 0, '/', '', false, false);
                    } else {
                        return response()->json(['code' => 200, 'message' => ['Login Success.'], 'token' => $response['access_token']]);
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

    public function webLogout(Request $request)
    {
        $accessTokenID = (new Parser)->parse($request->cookie('admin_token'))->getHeader('jti');
        DB::beginTransaction();
        try {
            DB::table('oauth_access_tokens')->where('id', $accessTokenID)->delete();
            DB::table('oauth_refresh_tokens')->where('access_token_id', $accessTokenID)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        $cookie = Cookie::forget('admin_token');
        // return redirect()->back()->withCookie($cookie);
        return redirect('/admin')->withCookie($cookie);
    }
}
