<?php

namespace App\Http\Controllers\Admin;

use App\Identification;
use App\Member;
use DB;
use Illuminate\Http\Request;

class MemberController extends BaseController
{
    public function verifyIdentificationCardIndex()
    {
        $identifications = Identification::select('*')
            ->with([
                'member' => function($member) {
                    $member->select('id', 'name', 'email');
                }
            ])
            ->whereHas('member', function($member) {
                $member->where('banned', 0);
            })
            ->get();

        return view('admin.verify-identification-card', compact('identifications'));
    }

    public function approveIdentificationCard($id)
    {
        $identification = Identification::select('*')
            ->where('verified', '<>', 1)
            ->whereHas('member', function($member) {
                $member->where('banned', 0);
            })
            ->find($id);

        if ($identification) {
            DB::beginTransaction();
            try {
                $identification->verified = 1;
                $identification->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Approve Identification Card success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Identification Card ID is invalid.']]);
        }
    }

    public function declineIdentificationCard($id)
    {
        $identification = Identification::select('*')
            ->where('verified', '<>', 2)
            ->whereHas('member', function($member) {
                $member->where('banned', 0);
            })
            ->find($id);

        if ($identification) {
            DB::beginTransaction();
            try {
                $identification->verified = 2;
                $identification->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Decline Identification Card success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Identification Card ID is invalid.']]);
        }
    }

    public function verifyOrganizerIndex()
    {
        $organizers = Member::select('id', 'name', 'email', 'document_file_name', 'verified', 'created_at')
            ->where('member_type', 2)
            ->where('banned', 0)
            ->get();

        return view('admin.verify-organizer', compact('organizers'));
    }

    public function approveOrganizer($id)
    {
        $organizer = Member::select('*')
            ->where('member_type', 2)
            ->where('verified', '<>', 1)
            ->where('banned', 0)
            ->find($id);

        if ($organizer) {
            DB::beginTransaction();
            try {
                $organizer->verified = 1;
                $organizer->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Approve Organizer success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Organizer ID is invalid.']]);
        }
    }

    public function declineOrganizer($id)
    {
        $organizer = Member::select('*')
            ->where('member_type', 2)
            ->where('verified', '<>', 2)
            ->where('banned', 0)
            ->find($id);

        if ($organizer) {
            DB::beginTransaction();
            try {
                $organizer->verified = 2;
                $organizer->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Decline Organizer success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Organizer ID is invalid.']]);
        }
    }

    public function suspendMemberIndex()
    {
        $members = Member::select('id', 'name', 'email', 'member_type', 'banned', 'created_at')
            ->whereIn('member_type', [1, 2])
            ->where('verified', 1)
            ->get();

        return view('admin.suspend-member', compact('members'));
    }

    public function banMember($id)
    {
        $member = Member::select('*')
            ->whereIn('member_type', [1, 2])
            ->where('verified', 1)
            ->where('banned', 0)
            ->find($id);

        if ($member) {
            DB::beginTransaction();
            try {
                $member->banned = 1;
                $member->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Ban Member success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Member ID is invalid.']]);
        }
    }

    public function activateMember($id)
    {
        $member = Member::select('*')
            ->whereIn('member_type', [1, 2])
            ->where('verified', 1)
            ->where('banned', 1)
            ->find($id);

        if ($member) {
            DB::beginTransaction();
            try {
                $member->banned = 0;
                $member->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Activate Member success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Member ID is invalid.']]);
        }
    }
}
