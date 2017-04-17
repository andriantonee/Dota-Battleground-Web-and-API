<?php

namespace App\Http\Controllers\Participant;

use App\Helpers\GuzzleHelper;
use App\Helpers\ValidatorHelper;
use App\Http\Controllers\Controller;
use App\Member;
use Cookie;
use DB;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private function getMemberType()
    {
        return 1;
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
                            ->json(['code' => 200, 'message' => 'Login Success.'], 200)
                            ->cookie('participant_token', $response['access_token'], 0, '/', '', false, false);
                    } else {
                        return response()->json(['code' => 400, 'token' => $response['access_token']], 200);
                    }
                } else {
                    return response()->json($response, 200);
                }
            } else {
                return response()->json(['code' => 400, 'message' => ['E-mail or Password is not valid.']], 200);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse], 200);
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
                return response()->json(['code' => 201, 'message' => ['Register success.']], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']], 200);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse], 200);
        }
    }

    public function profile(Request $request)
    {
        /* participant token no need Crypt class to decrypt */
        dd($request->cookie('participant_token') == 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImY1MzliNTkxMTVkNDFmZGE0YzhjYTVjZmQ2ZTA2NmM5MDdlZjY3MTYxMjE2Y2FlMDJlM2ZkMDI5YzdkMDY3NmJkMThmMzFiMWJkNzc1MmNmIn0.eyJhdWQiOiIxIiwianRpIjoiZjUzOWI1OTExNWQ0MWZkYTRjOGNhNWNmZDZlMDY2YzkwN2VmNjcxNjEyMTZjYWUwMmUzZmQwMjljN2QwNjc2YmQxOGYzMWIxYmQ3NzUyY2YiLCJpYXQiOjE0OTIzMzI2MDMsIm5iZiI6MTQ5MjMzMjYwMywiZXhwIjoyMTIzNDg0NjAzLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.Kf3s1EUIcAH5sJOgMpYfnZqRWUvNRp06QNW9xfY6_CmZdC5JtKaPsZ-guOBqqY15YqVWgrKI-hLXJIorLBpMi2dGreqtraWYIjuhjLsr1OhpJuFykXXg-VC_kntce4EbbV0ICrKCexWLeHJTyP7YOEHlinndn7UN0et1LndOtbqhcOFGxUnPSm_zbONdsUstALZoYbObspsYrOKLMh8194T8cyGwtJVoBE-2IyL8G6l_Gxtu34JsPoekAWp_UKBr_upNDWBW3XkuOYf7xOOXvwvZiq8FR8R-TiovpqiJgYKV0ASbMNi45jfn7KN_xsqAYhPec68sGm3abVuJ3-QsFTMqmB31-PM-hTJuYcX4gwhYyZQlgeyPe_jLJTNsj0LJZPdpYtuR0_0jmUzx6NrOpntbKgiHr-cuCA2Z4Jc3elfrJkfiqBAFz-1e6a4iAD9FHFHYuqwAI_aZu1x2T8Lb2_q6CMr4e2FSd8Z2HSs7HeWFbpC0oLEhQBhXOGQrRMjdamO92lM8YFaOzXPWFQgbRADnYR2YWQrGqZjU1FEI3RxteA54J1AZegTR5ZCslRgeaFBZ8lLvNeJlD7o7l_XewfiwbbXpUAhN5F2f216-ssh-IgtHsqBdLR005Qg-vqLBcuJIGdpeYoIbqFcXqUxtu6XdEy2NSvSblYTl27s-kAI');
    }
}
