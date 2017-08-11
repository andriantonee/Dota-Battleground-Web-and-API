<?php

namespace App\Http\Controllers\Organizer;

use App\Helpers\ValidatorHelper;
use Hash;
use Illuminate\Http\Request;
use Storage;

class ProfileController extends BaseController
{
    public function updateDocument(Request $request)
    {
        $member = $request->user();

        if ($member->verify == 1) {
            return response()->json(['code' => 400, 'message' => ['Member is already verified. Cannot update document anymore.']]);
        }

        $data = [
            'document' => $request->file('document')
        ];

        if (!$validatorResponse = ValidatorHelper::validateDocumentUpdateRequest($data)) {
            $path = $data['document']->storeAs('public/member/document', time().uniqid().$data['document']->hashName());

            if ($member->document_file_name) {
                Storage::delete('public/member/document/'.$member->document_file_name);
            }

            $member->document_file_name = substr($path, strlen('public/member/document') + 1);
            $member->verified = 0;
            $member->save();

            return response()->json(['code' => 200, 'message' => ['Upload Business Document success.']]);
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

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
