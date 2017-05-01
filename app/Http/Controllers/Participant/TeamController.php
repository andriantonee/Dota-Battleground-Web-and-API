<?php

namespace App\Http\Controllers\Participant;

use App\Helpers\ValidatorHelper;
use App\Member;
use App\Notification;
use App\NotificationMemberJoinTeam;
use App\NotificationTeamInvitation;
use App\Team;
use Carbon;
use DB;
use Illuminate\Http\Request;
use Storage;

class TeamController extends BaseController
{
    public function index(Request $request)
    {
        $member_id = null;
        if ($request->input('participant_model')) {
            $member_id = $request->input('participant_model')->id;
        }

        $teams = Team::with([
                'details' => function($details) use($member_id) {
                    $details->where('teams_details.members_id', $member_id);
                },
                'invitation_list' => function($invitation_list) use($member_id) {
                    $invitation_list->where('notifications_teams_invitations.invitation_status', 1)
                        ->whereHas('parent', function($parent) use($member_id) {
                            $parent->where('notifications.members_id', $member_id);
                        });
                }
            ])
            ->select('teams.id', 'teams.name', 'teams.picture_file_name', 'teams.join_password')
            ->withCount('details')
            ->orderBy('teams.created_at', 'DESC')
            ->get();

        return view('participant.team', compact('teams'));
    }

    public function show($id, Request $request)
    {
        $member_id = null;
        if ($request->input('participant_model')) {
            $member_id = $request->input('participant_model')->id;
        }

        $team = Team::select('id', 'name', 'picture_file_name', 'join_password', 'created_at')
            ->with([
                'details' => function($details) {
                    $details->select('members.id', 'members.name', 'members.steam32_id', 'members.picture_file_name', 'teams_details.members_privilege', 'teams_details.created_at')
                        ->orderBy('teams_details.members_privilege', 'DESC')
                        ->orderBy('teams_details.created_at', 'ASC');
                },
                'invitation_list' => function($invitation_list) use($member_id) {
                    $invitation_list->where('notifications_teams_invitations.invitation_status', 1)
                        ->whereHas('parent', function($parent) use($member_id) {
                            $parent->where('notifications.members_id', $member_id);
                        });
                }
            ])
            ->find($id);
        if ($team) {
            $inTeam = false;
            $isTeamLeader = false;
            if ($request->input('participant_model')) {
                $member_team = Member::find($request->input('participant_model')->id)->teams()->withPivot('members_privilege')->find($id);
                if ($member_team) {
                    $inTeam = true;
                    if ($member_team->pivot->members_privilege == 2) {
                        $isTeamLeader = true;
                    }
                }
            }

            return view('participant.team-detail', compact('team', 'inTeam', 'isTeamLeader'));
        } else {
            abort(404);
        }
    }

    public function store(Request $request)
    {
        $dataRequest = $request->all();
        $member = $request->user();

        $data = [
            'name' => $request->input('name'),
            'with_join_password' => $request->input('with_join_password') ?: 0
        ];
        if (array_key_exists('join_password', $dataRequest)) {
            $data['join_password'] = $request->input('join_password');
        }
        if ($request->hasFile('picture')) {
            if ($request->file('picture')->isValid()) {
                $data['picture'] = $request->file('picture');
            }
        }

        if (!$validatorResponse = ValidatorHelper::validateTeamCreateRequest($data)) {
            DB::beginTransaction();
            try {
                $path = null;
                if (array_key_exists('picture', $data)) {
                    $path = $data['picture']->store('public/team');
                }
                $team = new Team([
                    'name' => $data['name'],
                    'picture_file_name' => $path ? substr($path, strlen('public/team') + 1) : null,
                    'join_password' => $data['with_join_password'] == 1 ? $data['join_password'] : null
                ]);
                $team->save();
                $team->details()->attach($member->id, [
                    'members_privilege' => 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                DB::commit();
                return response()->json(['code' => 201, 'message' => ['Team has been created successfully.'], 'team' => ['url' => url('/').'/team/'.$team->id, 'name' => $team->name, 'count' => $team->details()->count('*'), 'picture_path' => $team->picture_file_name ? url('/').'/storage/team/'.$team->picture_file_name : url('/').'/img/default-group.png']]);
            } catch (\Exception $e) {
                DB::rollBack();
                if ($path) {
                    Storage::delete($path);
                }
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function update($id, Request $request)
    {
        $team = Team::find($id);
        if ($team) {
            $isTeamLeader = $request->user()->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
            if ($isTeamLeader) {
                // Continue
            } else {
                return response()->json(['code' => 403, 'message' => ['Member is not a leader of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        $dataRequest = $request->all();

        $data = [];
        if (array_key_exists('name', $dataRequest)) {
            $data['name'] = $dataRequest['name'];
        }
        if (array_key_exists('with_join_password', $dataRequest)) {
            $data['with_join_password'] = $dataRequest['with_join_password'];
        }
        if (array_key_exists('join_password', $dataRequest)) {
            $data['join_password'] = $dataRequest['join_password'];
        }

        if (!$validatorResponse = ValidatorHelper::validateTeamUpdateRequest($data, $id)) {
            DB::beginTransaction();
            try {
                if (array_key_exists('name', $data)) {
                    $team->name = $data['name'];
                }
                if (array_key_exists('with_join_password', $data)) {
                    if ($data['with_join_password'] == 0) {
                        $team->join_password = null;
                    } else {
                        $team->join_password = $data['join_password'];
                    }
                }
                $team->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Team has been updated.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function updatePicture($id, Request $request)
    {
        $team = Team::find($id);
        if ($team) {
            $isTeamLeader = $request->user()->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
            if ($isTeamLeader) {
                // Continue
            } else {
                return response()->json(['code' => 403, 'message' => ['Member is not a leader of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        $data = [
            'picture' => $request->file('picture')
        ];

        if (!$validatorResponse = ValidatorHelper::validateTeamPictureUpdateRequest($data)) {
            $path = $data['picture']->storeAs('public/team', time().uniqid().$data['picture']->hashName());
            if ($team->picture_file_name) {
                Storage::delete('public/team/'.$team->picture_file_name);
            }
            $team->picture_file_name = substr($path, strlen('public/team') + 1);
            $team->save();

            return response()->json(['code' => 200, 'message' => ['Picture has been updated.'], 'file_path' => url('/').'/storage/team/'.$team->picture_file_name]);
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function deletePicture($id, Request $request)
    {
        $team = Team::find($id);
        if ($team) {
            $isTeamLeader = $request->user()->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
            if ($isTeamLeader) {
                // Continue
            } else {
                return response()->json(['code' => 403, 'message' => ['Member is not a leader of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        if ($team->picture_file_name) {
            Storage::delete('public/team/'.$team->picture_file_name);
        }
        $team->picture_file_name = null;
        $team->save();

        return response()->json(['code' => 200, 'message' => ['Picture has been deleted.'], 'file_path' => url('/').'/img/default-group.png']);
    }

    public function join($id, Request $request)
    {
        $team = Team::find($id);
        $member = $request->user();

        if ($team) {
            $inTeam = $member->teams()->find($id) ? true : false;
            if ($inTeam) {
                return response()->json(['code' => 400, 'message' => ['You are already part of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }


        if ($team->join_password) {
            $data = [
                'join_password' => $request->input('join_password')
            ];
            if ($validatorResponse = ValidatorHelper::validateJoinTeamRequest($data, $team->join_password)) {
                return response()->json(['code' => 400, 'message' => $validatorResponse]);
            }
        }

        DB::beginTransaction();
        try {
            $team->details()->attach($member->id, [
                'members_privilege' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $leader = $team->details()->where('teams_details.members_privilege', 2)->first();
            $notification = new Notification([
                'read_status' => 0
            ]);
            $leader->notifications()->save($notification);
            $notification_detail = new NotificationMemberJoinTeam([
                'teams_id' => $team->id,
                'members_id' => $member->id
            ]);
            $notification->member_join_team()->save($notification_detail);

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['You are part of the team right now.'], 'count' => $team->details()->count('*')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function uninvitedMember($id, Request $request)
    {
        $team = Team::find($id);
        if ($team) {
            $isTeamLeader = $request->user()->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
            if ($isTeamLeader) {
                // Continue
            } else {
                return response()->json(['code' => 403, 'message' => ['Member is not a leader of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        $search_keyword = $request->input('search_keyword') ? (!is_array($request->input('search_keyword')) ? ((string) $request->input('search_keyword')) : '') : '';
        if ($search_keyword) {
            $members = Member::select('id', 'name', 'steam32_id', 'picture_file_name')
                ->doesntHave('notifications', 'and', function($notifications) use($id) {
                    $notifications->whereHas('team_invitation', function($team_invitation) use($id) {
                        $team_invitation->where('notifications_teams_invitations.teams_id', $id)
                            ->where('notifications_teams_invitations.invitation_status', 1);
                    });
                })
                ->doesntHave('teams', 'and', function($teams) use($id) {
                    $teams->where('teams.id', $id);
                })
                ->where('member_type', 1)
                ->where('name', 'LIKE', '%'.$search_keyword.'%')
                ->get();

            $members = $members->map(function($member, $key) {
                if (!$member->steam32_id) {
                    $member->steam32_id = '-';
                }
                if ($member->picture_file_name) {
                    $member->picture_file_name = asset('storage/member/'.$member->picture_file_name);
                } else {
                    $member->picture_file_name = asset('img/default-profile.jpg');
                }

                return $member;
            });

            return response()->json(['code' => 200, 'message' => ['Search uninvited member success.'], 'members' => $members]);
        } else {
            return response()->json(['code' => 200, 'message' => ['Search uninvited member success.'], 'members' => []]);
        }
    }

    public function inviteMember($id, $member_id, Request $request)
    {
        $team = Team::find($id);
        if ($team) {
            $isTeamLeader = $request->user()->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
            if ($isTeamLeader) {
                // Continue
            } else {
                return response()->json(['code' => 403, 'message' => ['Member is not a leader of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        $invited_member = Member::find((int) $member_id);
        if ($invited_member) {
            if ($invited_member->member_type == 1) {
                $is_exists_in_uninvited_member_list = Member::doesntHave('notifications', 'and', function($notifications) use($id) {
                        $notifications->whereHas('team_invitation', function($team_invitation) use($id) {
                            $team_invitation->where('notifications_teams_invitations.teams_id', $id)
                                ->where('notifications_teams_invitations.invitation_status', 1);
                        });
                    })
                    ->doesntHave('teams', 'and', function($teams) use($id) {
                        $teams->where('teams.id', $id);
                    })
                    ->where('id', (int) $member_id)
                    ->exists();
                if (!$is_exists_in_uninvited_member_list) {
                    return response()->json(['code' => 400, 'message' => ['Member that you want to invite is in your team or you already invite him/her.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['Member that you want to invite not exists.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Member that you want to invite not exists.']]);
        }

        DB::beginTransaction();
        try {
            $notification = new Notification([
                'read_status' => 0
            ]);
            $invited_member->notifications()->save($notification);
            $notification_detail = new NotificationTeamInvitation([
                'teams_id' => $team->id,
                'invitation_status' => 1
            ]);
            $notification->team_invitation()->save($notification_detail);

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['Team invitations has been delivered.']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function acceptInvitation($id, Request $request)
    {
        $team = Team::find($id);
        $member = $request->user();

        if ($team) {
            $inTeam = $member->teams()->find($id) ? true : false;
            if (!$inTeam) {
                $is_exists_in_team_invitation_list = $team->invitation_list()
                    ->whereHas('parent', function($parent) use($member) {
                        $parent->where('notifications.members_id', $member->id);
                    })
                    ->where('invitation_status', 1)
                    ->exists();
                if ($is_exists_in_team_invitation_list) {
                    // Continue
                } else {
                    return response()->json(['code' => 400, 'message' => ['You don\'t have invitation from this team.']]);
                }
            } else {
                return response()->json(['code' => 400, 'message' => ['You are already part of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        DB::beginTransaction();
        try {
            $member->notifications()
                ->whereHas('team_invitation', function($team_invitation) use($team) {
                    $team_invitation->where('notifications_teams_invitations.teams_id', $team->id)
                        ->where('notifications_teams_invitations.invitation_status', 1);
                })
                ->where('members_id', $member->id)
                ->update([
                    'read_status' => 1
                ]);
            $team->invitation_list()
                ->whereHas('parent', function($parent) use($member) {
                    $parent->where('notifications.members_id', $member->id);
                })
                ->where('invitation_status', 1)
                ->update([
                    'invitation_status' => 2
                ]);
            $team->details()->attach($member->id, [
                'members_privilege' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $leader = $team->details()->where('teams_details.members_privilege', 2)->first();
            $notification = new Notification([
                'read_status' => 0
            ]);
            $leader->notifications()->save($notification);
            $notification_detail = new NotificationMemberJoinTeam([
                'teams_id' => $team->id,
                'members_id' => $member->id
            ]);
            $notification->member_join_team()->save($notification_detail);

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['You are part of the team right now.'], 'count' => $team->details()->count('*')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function rejectInvitation($id, Request $request)
    {
        $team = Team::find($id);
        $member = $request->user();

        if ($team) {
            $is_exists_in_team_invitation_list = $team->invitation_list()
                ->whereHas('parent', function($parent) use($member) {
                    $parent->where('notifications.members_id', $member->id);
                })
                ->where('invitation_status', 1)
                ->exists();
            if ($is_exists_in_team_invitation_list) {
                // Continue
            } else {
                return response()->json(['code' => 400, 'message' => ['You don\'t have invitation from this team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        DB::beginTransaction();
        try {
            $member->notifications()
                ->whereHas('team_invitation', function($team_invitation) use($team) {
                    $team_invitation->where('notifications_teams_invitations.teams_id', $team->id)
                        ->where('notifications_teams_invitations.invitation_status', 1);
                })
                ->where('members_id', $member->id)
                ->update([
                    'read_status' => 1
                ]);
            $team->invitation_list()
                ->whereHas('parent', function($parent) use($member) {
                    $parent->where('notifications.members_id', $member->id);
                })
                ->where('invitation_status', 1)
                ->update([
                    'invitation_status' => 3
                ]);

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['Team invitation has been rejected.']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function kickMember($id, $member_id, Request $request)
    {
        $team = Team::find($id);
        $member = $request->user();
        $kicked_member = Member::find($member_id);

        if ($team) {
            $isTeamLeader = $member->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
            if ($isTeamLeader) {
                $isKickedMemberTeamLeader = $kicked_member->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
                if ($isKickedMemberTeamLeader) {
                    return response()->json(['code' => 400, 'message' => ['Member cannot kick Team Leader out of the team.']]);
                }
            } else {
                return response()->json(['code' => 403, 'message' => ['Member is not a leader of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        DB::beginTransaction();
        try {
            $team->details()->detach($kicked_member->id);

            DB::commit();
            return response()->json(['code' => 200, 'message' => [$kicked_member->name.' is no longer part of the team.']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }
}