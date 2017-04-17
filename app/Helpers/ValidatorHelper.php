<?php

namespace App\Helpers;

use App\Member;
use Validator;

class ValidatorHelper
{
    public static function validateLoginRequest(array $data)
    {
        $rule = [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'token_in_json' => 'integer|in:0,1'
        ];
        $message = [
            'email.required' => 'E-mail is required.',
            'email.string' => 'E-mail must be a string.',
            'email.email' => 'E-mail format is not a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'token_in_json.integer' => 'Token in JSON parameter must be an integer.',
            'token_in_json.in' => 'Token in JSON parameter value can support 0 or 1 only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateRegisterRequest(array $data, $member_type)
    {
        $rule = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email',
            'password' => 'required|string|min:6|confirmed'
        ];
        $message = [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'email.required' => 'E-mail is required.',
            'email.string' => 'E-mail must be a string.',
            'email.max' => 'E-mail has a maximum :max characters only.',
            'email.email' => 'E-mail format is not a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must contain minimum :min characters.',
            'password.confirmed' => 'Password confirmation does not match.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (Member::checkEmailExists($data['email'], $member_type)) {
                return ['E-mail '.$data['email'].' has been used.'];
            } else {
                return null;
            }
        }
    }
}
