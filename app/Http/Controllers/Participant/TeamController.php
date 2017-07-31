<?php

namespace App\Http\Controllers\Participant;

use App\Helpers\ValidatorHelper;
use App\Member;
use App\Notification;
use App\NotificationMemberJoinTeam;
use App\NotificationTeamInvitation;
use App\Team;
use App\Tournament;
use Carbon;
use DB;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Storage;

class TeamController extends BaseController
{
    private function getTeamList($member_id, $search_keyword = null)
    {
        $teams = Team::select('id', 'name', 'picture_file_name', 'join_password')
            ->withCount('details')
            ->with([
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
            ->orderBy('teams.created_at', 'DESC');
        if ($search_keyword) {
            $teams = $teams->where('name', 'LIKE', '%'.$search_keyword.'%');
        }

        return $teams->get();
    }

    public function index(Request $request)
    {
        $name = $request->input('name');

        $member_id = null;
        if ($request->segment(1) == 'api') {
            if ($request->bearerToken()) {
                $accessTokenID = (new Parser)->parse($request->bearerToken())->getHeader('jti');
                $access = DB::table('oauth_access_tokens')
                    ->where('id', $accessTokenID)
                    ->where('expires_at', '>', date('Y-m-d H:i:s'))
                    ->first();
                if ($access) {
                    $member_id = $access->user_id;
                }
            }
        } else {
            if ($request->input('participant_model')) {
                $member_id = $request->input('participant_model')->id;
            }
        }

        $teams = $this->getTeamList($member_id, $name);

        if ($request->segment(1) == 'api') {
            $teams_json = [];
            foreach ($teams as $key_team => $team) {
                $teams_json[$key_team] = [
                    'id' => $team->id,
                    'name' => $team->name,
                    'image' => $team->picture_file_name ? asset('storage/team/'.$team->picture_file_name) : asset('img/default-group.png'),
                    'in_team' => count($team->details) > 0 ? true : false,
                    'has_invitation' => count($team->invitation_list) > 0 ? true : false,
                    'number_of_members' => $team->details_count,
                    'join_code' => $team->join_password
                ];
            }

            return response()->json(['code' => 200, 'message' => ['Get Team success.'], 'teams' => $teams_json]);
        } else {
            return view('participant.team', compact('teams', 'name'));
        }
    }

    public function getMyTeam(Request $request)
    {
        $member = $request->user();
        $teams = $member->teams()
            ->select('teams.id', 'teams.name', 'teams.picture_file_name')
            ->orderBy('teams.created_at', 'ASC')
            ->withCount('details')
            ->get();
        $teams_json = [];
        foreach ($teams as $key_team => $team) {
            $teams_json[$key_team] = [
                'id' => $team->id,
                'name' => $team->name,
                'image' => $team->picture_file_name ? asset('storage/team/'.$team->picture_file_name) : asset('img/default-group.png'),
                'number_of_members' => $team->details_count
            ];
        }

        return response()->json(['code' => 200, 'message' => ['Get Schedules success.'], 'teams' => $teams_json]);
    }

    public function show($id, Request $request)
    {
        $member_id = null;
        if ($request->segment(1) == 'api') {
            if ($request->bearerToken()) {
                $accessTokenID = (new Parser)->parse($request->bearerToken())->getHeader('jti');
                $access = DB::table('oauth_access_tokens')
                    ->where('id', $accessTokenID)
                    ->where('expires_at', '>', date('Y-m-d H:i:s'))
                    ->first();
                if ($access) {
                    $member_id = $access->user_id;
                }
            }
        } else {
            if ($request->input('participant_model')) {
                $member_id = $request->input('participant_model')->id;
            }
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
            if ($member_id) {
                $member_team = Member::find($member_id)->teams()->withPivot('members_privilege')->find($id);
                if ($member_team) {
                    $inTeam = true;
                    if ($member_team->pivot->members_privilege == 2) {
                        $isTeamLeader = true;
                    }
                }
            }
            if ($isTeamLeader) {
                $team->invited_members = $team->invitation_list()
                    ->select('notifications_id', 'teams_id')
                    ->with([
                        'parent' => function($parent) {
                            $parent->select('id', 'members_id', 'created_at')
                                ->with([
                                    'member' => function($member) {
                                        $member->select('id', 'name', 'steam32_id', 'picture_file_name');
                                    }
                                ]);
                        }
                    ])
                    ->where('invitation_status', 1)
                    ->get();
            } else {
                $team->invited_members = [];
            }
            $team->tournaments_schedules = $team->tournaments_registrations()
                ->select('id', 'tournaments_id', 'teams_id', 'created_at')
                ->with([
                    'tournament' => function($tournament) use($team) {
                        $tournament->select('tournaments.id', 'tournaments.name', DB::raw('MAX(`matches`.`round`) AS max_round'))
                            ->join('matches', 'tournaments.id', '=', 'matches.tournaments_id')
                            ->with([
                                'matches' => function($matches) use($team) {
                                    $matches->select('id', 'tournaments_id', 'scheduled_time', 'round')
                                        ->with([
                                            'participants' => function($participants) {
                                                $participants->select('tournaments_registrations.teams_id', 'matches_participants.side')
                                                    ->with([
                                                        'team' => function($team) {
                                                            $team->select('id', 'name');
                                                        }
                                                    ])
                                                    ->whereIn('matches_participants.side', [1, 2])
                                                    ->orderBy('matches_participants.side', 'ASC');
                                            }
                                        ])
                                        ->where('scheduled_time', '>', date('Y-m-d H:i:s'))
                                        ->whereHas('participants', function($participants) use($team) {
                                            $participants->where('tournaments_registrations.teams_id', $team->id);
                                        })
                                        ->orderBy('round', 'ASC');
                                }
                            ])
                            ->groupBy('tournaments.id');
                    }
                ])
                ->whereHas('tournament', function($tournament) use($team) {
                    $tournament->where('start', 1)
                        ->whereHas('matches', function($matches) use($team) {
                            $matches->where('scheduled_time', '>', date('Y-m-d H:i:s'))
                                ->whereHas('participants', function($participants) use($team) {
                                    $participants->where('tournaments_registrations.teams_id', $team->id);
                                });
                        });
                })
                ->orderBy('created_at', 'DESC')
                ->get();
            // dd($team->tournaments_schedules->toArray());
            $team->tournaments_registrations = $team->tournaments_registrations()
                ->select('id', 'tournaments_id', 'teams_id', 'created_at')
                ->with([
                    'tournament' => function($tournament) {
                        $tournament->select('id', 'name', 'logo_file_name', 'registration_closed', 'start_date', 'end_date', 'start', 'complete', 'members_id')
                            ->with([
                                'owner' => function($owner) {
                                    $owner->select('id', 'name');
                                }
                            ]);
                    }
                ])
                ->orderBy('created_at', 'DESC')
                ->get();

            if ($request->segment(1) == 'api') {
                $team_json = [
                    'id' => $team->id,
                    'name' => $team->name,
                    'image' => $team->picture_file_name ? asset('storage/team/'.$team->picture_file_name) : asset('img/default-group.png'),
                    'join_code' => $team->join_password
                ];
                
                $team_details_json = [];
                foreach ($team->details as $key_team_detail => $team_detail) {
                    $team_details_json[$key_team_detail] = [
                        'id' => $team_detail->id,
                        'name' => $team_detail->name,
                        'steam32_id' => $team_detail->steam32_id ? $team_detail->steam32_id : '-',
                        'image' => $team_detail->picture_file_name ? asset('storage/member/'.$team_detail->picture_file_name) : asset('img/default-profile.jpg'),
                        'status' => ($team_detail->members_privilege == 2 ? 'Captain' : ''),
                        'joined_at' => strtotime($team_detail->created_at)
                    ];
                }
                
                return response()->json(['code' => 200, 'message' => ['Get Team Detail success.'], 'team' => $team_json, 'in_team' => $inTeam, 'is_leader' => $isTeamLeader, 'teams_details' => $team_details_json]);
            } else {
                return view('participant.team-detail', compact('team', 'inTeam', 'isTeamLeader'));
            }
        } else {
            if ($request->segment(1) == 'api') {
                return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
            }
            else {
                abort(404);
            }
        }
    }

    public function getMyTeamDetail($id, Request $request)
    {
        $member = $request->user();
        $team = $member->teams()
            ->select('teams.id', 'teams.name', 'teams.picture_file_name', 'teams.join_password')
            ->find($id);
        if ($team) {
            $is_leader_json = false;
            if ($member->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2) {
                $is_leader_json = true;
            }
            $team_json = [
                'id' => $team->id,
                'name' => $team->name,
                'image' => $team->picture_file_name ? asset('storage/team/'.$team->picture_file_name) : asset('img/default-group.png'),
                'join_code' => $team->join_password
            ];
            $teams_details = $team->details()
                ->select('members.id', 'members.name', 'members.steam32_id', 'members.picture_file_name', 'teams_details.members_privilege', 'teams_details.created_at')
                ->orderBy('teams_details.members_privilege', 'DESC')
                ->orderBy('teams_details.created_at', 'ASC')
                ->get();
            $team_details_json = [];
            foreach ($teams_details as $key_team_detail => $team_detail) {
                $team_details_json[$key_team_detail] = [
                    'id' => $team_detail->id,
                    'name' => $team_detail->name,
                    'steam32_id' => $team_detail->steam32_id ? $team_detail->steam32_id : '-',
                    'image' => $team_detail->picture_file_name ? asset('storage/member/'.$team_detail->picture_file_name) : asset('img/default-profile.jpg'),
                    'status' => ($team_detail->members_privilege == 2 ? 'Captain' : ''),
                    'joined_at' => strtotime($team_detail->created_at)
                ];
            }

            return response()->json(['code' => 200, 'message' => ['Get Team Detail success.'], 'team' => $team_json, 'is_leader' => $is_leader_json, 'teams_details' => $team_details_json]);
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
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
                    $path = $data['picture']->storeAs('public/team', time().uniqid().$data['picture']->hashName());
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

    public function searchTeam(Request $request)
    {
        $member_id = null;
        $isLogin = false;
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $accessTokenID = (new Parser)->parse($bearerToken)->getHeader('jti');
            $accessToken = DB::table('oauth_access_tokens')->select('user_id')->where('id', $accessTokenID)->first();
            if ($accessToken) {
                $member_id = $accessToken->user_id;
                $isLogin = true;
            }
        }

        $search_keyword = $request->input('search_keyword') ? (!is_array($request->input('search_keyword')) ? ((string) $request->input('search_keyword')) : '') : '';
        $teams = null;
        if ($search_keyword) {
            $teams = $this->getTeamList($member_id, $search_keyword);
        } else {
            $teams = $this->getTeamList($member_id);
        }

        if ($teams) {
            $teams = $teams->map(function($team, $key) {
                $team->url = url('/team/'.$team->id);
                if ($team->picture_file_name) {
                    $team->picture_file_name = asset('storage/team/'.$team->picture_file_name);
                } else {
                    $team->picture_file_name = asset('img/default-group.png');
                }
                if ($team->join_password) {
                    $team->join_password = true;
                } else {
                    $team->join_password = false;
                }

                return $team;
            });
        }

        return response()->json(['code' => 200, 'message' => ['Search team name success.'], 'teams' => $teams, 'isLogin' => $isLogin]);
    }

    public function member($id, Request $request)
    {
        $team = Team::find($id);
        $member = $request->user();

        if ($team) {
            $isTeamLeader = $member->teams()->withPivot('members_privilege')->find($id)->pivot->members_privilege == 2;
            if ($isTeamLeader) {
                // Continue
            } else {
                return response()->json(['code' => 403, 'message' => ['Member is not a leader of the team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Team ID is invalid.']]);
        }

        $tournament_id = $request->input('tournament_id') ? (!is_array($request->input('tournament_id')) ? $request->input('tournament_id') : null) : null;
        if ($tournament_id) {
            $tournament = Tournament::find($tournament_id);
            if ($tournament) {
                if ($tournament->need_identifications) {
                    $must_have_identification = true;
                } else {
                    $must_have_identification = false;
                }
            } else {
                $must_have_identification = false;
            }
        } else {
            $must_have_identification = false;
        }

        if ($must_have_identification) {
            $details = $team->details()
                ->select('id', 'name', 'picture_file_name')
                ->whereNotNull('steam32_id')
                ->whereHas('identifications')
                ->orderBy('name', 'ASC')
                ->get();
        } else {
            $details = $team->details()
                ->select('id', 'name', 'picture_file_name')
                ->whereNotNull('steam32_id')
                ->orderBy('name', 'ASC')
                ->get();
        }

        $details = $details->map(function($detail, $key) {
            if ($detail->picture_file_name) {
                $detail->picture_file_name = asset('storage/member/'.$detail->picture_file_name);
            } else {
                $detail->picture_file_name = asset('img/default-profile.jpg');
            }

            return $detail;
        });

        return response()->json(['code' => 200, 'message' => ['Fetch team member success.'], 'members' => $details]);
    }
}
