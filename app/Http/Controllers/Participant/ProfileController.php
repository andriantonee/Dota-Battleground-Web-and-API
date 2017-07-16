<?php

namespace App\Http\Controllers\Participant;

use App\Helpers\ValidatorHelper;
use App\Identification;
use App\Member;
use DB;
use Hash;
use Illuminate\Http\Request;
use Storage;

class ProfileController extends BaseController
{
    public function index(Request $request)
    {
        $member = Member::find($request->input('participant_model')->id);
        $identification_file_name = $member->identifications()
            ->orderBy('created_at', 'DESC')
            ->value('identification_file_name');
        $teams = $member->teams()
            ->select('teams.id', 'teams.name', 'teams.picture_file_name')
            ->orderBy('teams.created_at', 'ASC')
            ->withCount('details')
            ->get();
        $schedules = $member->tournaments_registrations()
            ->select('tournaments_registrations.id', 'tournaments_registrations.tournaments_id', 'tournaments_registrations.teams_id', 'tournaments_registrations.created_at')
            ->with([
                'tournament' => function($tournament) use($member) {
                    $tournament->select('tournaments.id', 'tournaments.name', DB::raw('MAX(`matches`.`round`) AS max_round'))
                        ->join('matches', 'tournaments.id', '=', 'matches.tournaments_id')
                        ->with([
                            'matches' => function($matches) use($member) {
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
                                    ->whereHas('participants', function($participants) use($member) {
                                        $participants->whereHas('members', function($members) use($member) {
                                            $members->where('members.id', $member->id); 
                                        });
                                    })
                                    ->orderBy('round', 'ASC');
                            }
                        ])
                        ->groupBy('tournaments.id');
                }
            ])
            ->whereHas('tournament', function($tournament) use($member) {
                $tournament->where('start', 1)
                    ->whereHas('matches', function($matches) use($member) {
                        $matches->whereHas('participants', function($participants) use($member) {
                            $participants->whereHas('members', function($members) use($member) {
                                $members->where('members.id', $member->id); 
                            });
                        });
                    });
            })
            ->orderBy('tournaments_registrations.created_at', 'DESC')
            ->get();
        $registrations = $member->teams()
            ->select('teams.id', 'teams.name')
            ->with([
                'tournaments_registrations' => function($tournaments_registrations) {
                    $tournaments_registrations->select('id', 'tournaments_id', 'teams_id', 'created_at')
                        ->with([
                            'tournament' => function($tournament) {
                                $tournament->select('id', 'name');
                            },
                            'confirmation' => function($confirmation) {
                                $confirmation->select('tournaments_registrations_id')
                                    ->with([
                                        'approval' => function($approval) {
                                            $approval->select('tournaments_registrations_confirmations_id', 'status');
                                        }
                                    ]);
                            }
                        ])
                        ->withCount('members')
                        ->orderBy('created_at', 'DESC');
                }
            ])
            ->whereHas('details', function($details) use($member) {
                $details->where('members.id', $member->id)
                    ->where('teams_details.members_privilege', 2);
            })
            ->whereHas('tournaments_registrations')
            ->orderBy('teams.created_at', 'ASC')
            ->get();
        $in_progress_tournaments = $member->tournaments_registrations()
            ->select('tournaments_registrations.id', 'tournaments_registrations.tournaments_id', 'tournaments_registrations.teams_id', 'tournaments_registrations.created_at', 'tournaments_registrations_details.qr_identifier')
            ->with([
                'tournament' => function($tournament) {
                    $tournament->select('id', 'name', 'logo_file_name', 'registration_closed', 'start_date', 'end_date', 'start', 'complete', 'members_id');
                },
                'team' => function($team) {
                    $team->select('id', 'name');
                }
            ])
            ->whereHas('tournament', function($tournament) {
                $tournament->where('start', 1)
                    ->where('complete', 0);
            })
            ->whereHas('confirmation', function($confirmation) {
                $confirmation->whereHas('approval', function($approval) {
                    $approval->where('status', 1);
                });
            })
            ->orderBy('tournaments_registrations.created_at', 'DESC')
            ->get();
        $in_progress_tournaments = $in_progress_tournaments->map(function($tournaments_registrations, $key) {
            if ($tournaments_registrations->qr_identifier) {
                $qr_file_name = md5($tournaments_registrations->qr_identifier);
                $tournaments_registrations->qr_identifier = $qr_file_name;
            }

            return $tournaments_registrations;
        });
        $completed_tournaments = $member->tournaments_registrations()
            ->select('tournaments_registrations.id', 'tournaments_registrations.tournaments_id', 'tournaments_registrations.teams_id', 'tournaments_registrations.created_at')
            ->with([
                'tournament' => function($tournament) {
                    $tournament->select('id', 'name', 'logo_file_name', 'registration_closed', 'start_date', 'end_date', 'start', 'complete', 'members_id');
                },
                'team' => function($team) {
                    $team->select('id', 'name');
                }
            ])
            ->whereHas('tournament', function($tournament) {
                $tournament->where('start', 1)
                    ->where('complete', 1);
            })
            ->whereHas('confirmation', function($confirmation) {
                $confirmation->whereHas('approval', function($approval) {
                    $approval->where('status', 1);
                });
            })
            ->orderBy('tournaments_registrations.created_at', 'DESC')
            ->get();

        return view('participant.profile', compact('identification_file_name', 'teams', 'schedules', 'registrations', 'in_progress_tournaments', 'completed_tournaments'));
    }

    public function getProfile(Request $request)
    {
        $member = $request->user();
        $member_json = [
            'id' => $member->id,
            'email' => $member->email,
            'name' => $member->name,
            'steam32_id' => $member->steam32_id,
            'image' => $member->picture_file_name ? asset('storage/member/'.$participant->picture_file_name) : asset('img/default-profile.jpg')
        ];

        return response()->json(['code' => 200, 'message' => ['Get Profile success.'], 'user' => $member_json]);
    }

    public function getMySchedule(Request $request)
    {
        $member = $request->user();
        $schedules = $member->tournaments_registrations()
            ->select('tournaments_registrations.id', 'tournaments_registrations.tournaments_id', 'tournaments_registrations.teams_id', 'tournaments_registrations.created_at')
            ->with([
                'tournament' => function($tournament) use($member) {
                    $tournament->select('tournaments.id', 'tournaments.name', DB::raw('MAX(`matches`.`round`) AS max_round'))
                        ->join('matches', 'tournaments.id', '=', 'matches.tournaments_id')
                        ->with([
                            'matches' => function($matches) use($member) {
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
                                    ->whereHas('participants', function($participants) use($member) {
                                        $participants->whereHas('members', function($members) use($member) {
                                            $members->where('members.id', $member->id); 
                                        });
                                    })
                                    ->orderBy('round', 'ASC');
                            }
                        ])
                        ->groupBy('tournaments.id');
                }
            ])
            ->whereHas('tournament', function($tournament) use($member) {
                $tournament->where('start', 1)
                    ->whereHas('matches', function($matches) use($member) {
                        $matches->whereHas('participants', function($participants) use($member) {
                            $participants->whereHas('members', function($members) use($member) {
                                $members->where('members.id', $member->id); 
                            });
                        });
                    });
            })
            ->orderBy('tournaments_registrations.created_at', 'DESC')
            ->get();

        $schedules_json = [];
        foreach ($schedules as $key_schedule => $registration) {
            foreach ($registration->matches as $key_match => $match) {
                $round_name = 'Finals';
                if ($match->round < 0) {
                    $round_name = 'Lower Round '.abs($match->round);
                } else if ($match->round == 0) {
                    $round_name = 'Bronze Match';
                } else if ($match->round < $registration->tournament->max_round - 1) {
                    $round_name = 'Semifinals';
                }

                $player_1 = 'TBD';
                $player_2 = 'TBD';
                foreach ($match->participants as $participant) {
                    if ($participant->side == 1) {
                        $player_1 = $participant->team->name;
                    } else if ($participant->side == 2) {
                        $player_2 = $participant->team->name;
                    }
                }

                $schedules_json[] = [
                    'tournament_name' => $registration->tournament->name,
                    'round_name' => $round_name,
                    'player_1' => $player_1,
                    'player_2' => $player_2,
                    'scheduled_time' => strtotime($match->scheduled_time)
                ];
            }
        }

        return response()->json(['code' => 200, 'message' => ['Get Schedules success.'], 'schedules' => $schedules_json]);
    }

    public function getMyIdentification(Request $request)
    {
        $member = $request->user();
        $identification = $member->identifications()
            ->orderBy('created_at', 'DESC')
            ->value('identification_file_name');
        $identification = [
            'image' => $identification ? asset('storage/member/identification/'.$identification) : asset('img/holder328x178.png')
        ];

        return response()->json(['code' => 200, 'message' => ['Get Identification success.'], 'identification' => $identification]);
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

    public function updateProfilePicture(Request $request)
    {
        $member = $request->user();

        $data = [
            'profile_picture_file' => $request->file('profile_picture')
        ];
        
        if (!$validatorResponse = ValidatorHelper::validateProfilePictureUpdateRequest($data)) {
            $path = $data['profile_picture_file']->storeAs('public/member', time().uniqid().$data['profile_picture_file']->hashName());
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
            $path = $data['identification_file']->storeAs('public/member/identification', time().uniqid().$data['identification_file']->hashName());
            $identification = new Identification(['identification_file_name' => substr($path, strlen('public/member/identification') + 1)]);
            $member->identifications()->save($identification);

            return response()->json(['code' => 200, 'message' => ['Identity Card has been updated.'], 'file_path' => url('/').'/storage/member/identification/'.$identification->identification_file_name]);
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }
}
