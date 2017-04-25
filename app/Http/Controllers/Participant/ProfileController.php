<?php

namespace App\Http\Controllers\Participant;

use App\Helpers\ValidatorHelper;
use App\Identification;
use App\Member;
use DB;
use Illuminate\Http\Request;
use Storage;

class ProfileController extends BaseController
{
    public function index(Request $request)
    {
        $identification_file_name = Member::find($request->input('participant_model')->id)->identifications()->orderBy('created_at', 'DESC')->value('identification_file_name');

        return view('participant.profile', compact('identification_file_name'));
    }

    public function show(Request $request)
    {

    }

    public function update(Request $request)
    {
        $dataRequest = $request->all();
        $member = $request->user();

        $data = [];
        if (array_key_exists('name', $dataRequest)) {
            $data['name'] = $request->input('name');
        }
        if (array_key_exists('email', $dataRequest)) {
            $data['email'] = $request->input('email');
        }
        if (array_key_exists('steam32_id', $dataRequest)) {
            $data['steam32_id'] = $request->input('steam32_id');
        }
        $member_type = $this->getMemberType();
        $member_id = $member->id;

        if (!$validatorResponse = ValidatorHelper::validateProfileUpdateRequest($data, $member_type, $member_id)) {
            DB::beginTransaction();
            try {
                if (array_key_exists('name', $data)) {
                    $member->name = $data['name'];
                }
                if (array_key_exists('email', $data)) {
                    $member->email = $data['email'];
                }
                if (array_key_exists('steam32_id', $data)) {
                    $member->steam32_id = $data['steam32_id'];
                }
                $member->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Profile has been updated.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function updateProfilePicture(Request $request)
    {
        $member = $request->user();

        $data = [
            'profile_picture_file' => $request->file('profile_picture')
        ];
        
        if (!$validatorResponse = ValidatorHelper::validateProfilePictureUpdateRequest($data)) {
            $path = $data['profile_picture_file']->store('public/member');
            if ($member->picture_file_name) {
                Storage::delete('public/member/'.$member->picture_file_name);
            }
            $member->picture_file_name = substr($path, strlen('public/member') + 1);
            $member->save();

            return response()->json(['code' => 200, 'message' => ['Profile Picture has been updated.'], 'file_path' => url('/').'/storage/member/'.$member->picture_file_name]);
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function deleteProfilePicture(Request $request)
    {
        $member = $request->user();

        if ($member->picture_file_name) {
            Storage::delete('public/member/'.$member->picture_file_name);
        }
        $member->picture_file_name = null;
        $member->save();

        return response()->json(['code' => 200, 'message' => ['Profile Picture has been deleted.'], 'file_path' => url('/').'/img/default-profile.jpg']);
    }

    public function updateIdentification(Request $request)
    {
        $member = $request->user();

        $data = [
            'identification_file' => $request->file('identity_card')
        ];

        if (!$validatorResponse = ValidatorHelper::validateIdentificationUpdateRequest($data)) {
            $path = $data['identification_file']->store('public/member/identification');
            $identification = new Identification(['identification_file_name' => substr($path, strlen('public/member/identification') + 1)]);
            $member->identifications()->save($identification);

            return response()->json(['code' => 200, 'message' => ['Identity Card has been updated.'], 'file_path' => url('/').'/storage/member/identification/'.$identification->identification_file_name]);
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }
}
