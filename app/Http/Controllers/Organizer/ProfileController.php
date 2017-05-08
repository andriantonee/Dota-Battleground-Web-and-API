<?php

namespace App\Http\Controllers\Organizer;

use App\Helpers\ValidatorHelper;
use Hash;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function updatePassword(Request $request)
    {
        $member = $request->user();

        $data = [
            'old_password' => $request->input('old_password'),
            'new_password' => $request->input('new_password'),
            'new_password_confirmation' => $request->input('new_password_confirmation')
        ];

        if (!$validatorResponse = ValidatorHelper::validatePasswordUpdateRequest($data, $member->password)) {
            $member->password = Hash::make($data['new_password']);
            $member->save();

            return response()->json(['code' => 200, 'message' => ['Password has been updated.']]);
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }
}
