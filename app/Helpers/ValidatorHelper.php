<?php

namespace App\Helpers;

use App\Member;
use App\Team;
use Hash;
use Validator;

class ValidatorHelper
{
    public static function validateLoginRequest(array $data)
    {
        $rule = [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'token_in_json' => 'required|integer|in:0,1'
        ];
        $message = [
            'email.required' => 'E-mail is required.',
            'email.string' => 'E-mail must be a string.',
            'email.email' => 'E-mail format is not a valid email address.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'token_in_json.required' => 'Token in JSON parameter is required.',
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

    public static function validateProfileUpdateRequest(array $data, $member_type, $member_id)
    {
        $rule = [
            'name' => 'filled|string|max:255',
            'email' => 'filled|string|max:255|email',
            'steam32_id' => 'string|max:255'
        ];
        $message = [
            'name.filled' => 'Name must not empty.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'email.filled' => 'E-mail must not empty.',
            'email.string' => 'E-mail must be a string.',
            'email.max' => 'E-mail has a maximum :max characters only.',
            'email.email' => 'E-mail format is not a valid email address.',
            'steam32_id.string' => 'Steam ID 32-bit must be a string.',
            'steam32_id.max' => 'Steam ID 32-bit has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (array_key_exists('email', $data)) {
                if (Member::checkEmailExists($data['email'], $member_type, $member_id)) {
                    return ['E-mail '.$data['email'].' has been used.'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }

    public static function validatePasswordUpdateRequest(array $data, $password)
    {
        $rule = [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed'
        ];
        $message = [
            'old_password.required' => 'Old Password is required.',
            'old_password.string' => 'Old Password must be a string.',
            'new_password.required' => 'New Password is required.',
            'new_password.string' => 'New Password must be a string.',
            'new_password.min' => 'New Password must contain minimum :min characters.',
            'new_password.confirmed' => 'New Password confirmation does not match.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (!Hash::check($data['old_password'], $password)) {
                return ['Old Password is not valid.'];
            } else {
                return null;
            }
        }
    }

    public static function validateProfilePictureUpdateRequest(array $data)
    {
        $rule = [
            'profile_picture_file' => 'required|mimes:jpeg,png|max:512'
        ];
        $message = [
            'profile_picture_file.required' => 'Profile Picture file not found.',
            'profile_picture_file.mimes' => 'Profile Picture file only support jpeg and png file type.',
            'profile_picture_file.max' => 'Profile Picture file has passed :max Kb.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateIdentificationUpdateRequest(array $data)
    {
        $rule = [
            'identification_file' => 'required|mimes:jpeg,png|max:1024'
        ];
        $message = [
            'identification_file.required' => 'Profile Picture file not found.',
            'identification_file.mimes' => 'Profile Picture file only support jpeg and png file type.',
            'identification_file.max' => 'Profile Picture file has passed :max Kb.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateTeamCreateRequest(array $data)
    {
        $rule = [
            'picture' => 'filled|mimes:jpeg,png|max:1024',
            'name' => 'required|string|max:255',
            'with_join_password' => 'required|integer|in:0,1',
            'join_password' => 'required_if:with_join_password,1|string|max:255'
        ];
        $message = [
            'picture.filled' => 'Picture file not found.',
            'picture.mimes' => 'Picture file only support jpeg and png file type.',
            'picture.max' => 'Picture file has passed :max Kb.',
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'with_join_password.required' => 'With Join Password parameter is required.',
            'with_join_password.integer' => 'With Join Password parameter must be an integer.',
            'with_join_password.in' => 'With Join Password parameter value can support 0 or 1 only.',
            'join_password.required_if' => 'Join Code is required.',
            'join_password.string' => 'Join Code must be a string.',
            'join_password.max' => 'Join Code has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (Team::checkNameExists($data['name'])) {
                return ['Name '.$data['name'].' has been used.'];
            } else {
                return null;
            }
        }
    }

    public static function validateTeamUpdateRequest(array $data, $team_id)
    {
        $rule = [
            'name' => 'filled|string|max:255',
            'with_join_password' => 'filled|integer|in:0,1',
            'join_password' => 'required_if:with_join_password,1|string|max:255'
        ];
        $message = [
            'name.filled' => 'Name must not empty.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name has a maximum :max characters only.',
            'with_join_password.filled' => 'With Join Password parameter must not empty.',
            'with_join_password.integer' => 'With Join Password parameter must be an integer.',
            'with_join_password.in' => 'With Join Password parameter value can support 0 or 1 only.',
            'join_password.required_if' => 'Join Code must not empty.',
            'join_password.string' => 'Join Code must be a string.',
            'join_password.max' => 'Join Code has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if (array_key_exists('name', $data)) {
                if (Team::checkNameExists($data['name'], $team_id)) {
                    return ['Name '.$data['name'].' has been used.'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }
    }

    public static function validateTeamPictureUpdateRequest(array $data)
    {
        $rule = [
            'picture' => 'required|mimes:jpeg,png|max:1024'
        ];
        $message = [
            'picture.required' => 'Picture file not found.',
            'picture.mimes' => 'Picture file only support jpeg and png file type.',
            'picture.max' => 'Picture file has passed :max Kb.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            return null;
        }
    }

    public static function validateJoinTeamRequest(array $data, $join_password)
    {
        $rule = [
            'join_password' => 'required|string|max:255'
        ];
        $message = [
            'join_password.required' => 'Join Code must not empty.',
            'join_password.string' => 'Join Code must be a string.',
            'join_password.max' => 'Join Code has a maximum :max characters only.'
        ];
        $validator = Validator::make($data, $rule, $message);

        if ($validator->fails()) {
            return $validator->errors()->all();
        } else {
            if ($data['join_password'] !== $join_password) {
                return ['Join Code is invalid.'];
            } else {
                return null;
            }
        }
    }
}
