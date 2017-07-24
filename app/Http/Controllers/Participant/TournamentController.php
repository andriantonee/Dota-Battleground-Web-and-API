<?php

namespace App\Http\Controllers\Participant;

use App\City;
use App\Helpers\ValidatorHelper;
use App\Member;
use App\Team;
use App\Tournament;
use App\TournamentRegistration;
use App\TournamentRegistrationConfirmation;
use Carbon;
use DB;
use Illuminate\Http\Request;
use Storage;

class TournamentController extends BaseController
{
    public function index(Request $request)
    {
        $name = $request->input('name');
        $status = $request->input('status') ?: 1;
        $order = $request->input('order') ?: 1;
        $price = $request->input('price');
        $start_date = $request->input('start_date');
        $selected_city = $request->input('city');

        $cities = City::select('id', 'name')->get();
        $tournaments = Tournament::select('id', 'name', 'logo_file_name', 'type', 'cities_id', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'start', 'complete', 'members_id')
            ->with([
                'owner' => function($owner) {
                    $owner->select('id', 'name');
                }
            ])
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            });

        if ($name) {
            $tournaments = $tournaments->where('name', 'LIKE', '%'.$name.'%');
        }

        if ($status == 2) {
            $tournaments = $tournaments->where(function($status) {
                $status->orWhere('registration_closed', '>=', date('Y-m-d H:i:s'))
                    ->orWhere('start', 0);
            });
        } else if ($status == 3) {
            $tournaments = $tournaments->where('start', 1)
                ->where('complete', 0);
        } else if ($status == 4) {
            $tournaments = $tournaments->where('start', 1)
                ->where('complete', 1);
        }

        if ($order == 1) {
            $tournaments = $tournaments->orderBy('name', 'ASC');
        } else if ($order == 2) {
            $tournaments = $tournaments->orderBy('start_date', 'ASC');
        } else if ($order == 3) {
            $tournaments = $tournaments->orderBy('registration_closed', 'ASC');
        }

        if ($price == 1) {
            $tournaments = $tournaments->where('entry_fee', '<', '50000');
        } else if ($price == 2) {
            $tournaments = $tournaments->where('entry_fee', '>=', '50000')
                ->where('entry_fee', '<=', '100000');
        } else if ($price == 3) {
            $tournaments = $tournaments->where('entry_fee', '>=', '100000')
                ->where('entry_fee', '<=', '150000');
        } else if ($price == 4) {
            $tournaments = $tournaments->where('entry_fee', '>', '150000');
        }

        if ($start_date) {
            $tournaments = $tournaments->where('start_date', date('Y-m-d', strtotime(str_replace('/', '-', $start_date))));
        }

        if ($selected_city) {
            $tournaments = $tournaments->where('cities_id', $selected_city);
        }

        $tournaments = $tournaments->get();

        if ($request->segment(1) == 'api') {
            $tournaments_json = [];
            foreach ($tournaments as $key_tournament => $tournament) {
                $tournaments_json[$key_tournament] = [
                    'id' => $tournament->id,
                    'image' => asset('storage/tournament/'.$tournament->logo_file_name),
                    'name' => $tournament->name,
                    'start_date' => strtotime($tournament->start_date),
                    'end_date' => strtotime($tournament->end_date),
                    'registration_closed' => strtotime($tournament->registration_closed),
                    'entry_fee' => $tournament->entry_fee
                ];

                $tournament_status = '';
                if (date('Y-m-d H:i:s' <= $tournament->registration_closed)) {
                    $tournament_status = 'Upcoming';
                } else {
                    if ($tournament->start == 0) {
                        $tournament_status = 'Upcoming';
                    } else {
                        if ($tournament->complete == 0) {
                            $tournament_status = 'In Progress';
                        } else {
                            $tournament_status = 'Complete';
                        }
                    }
                }
                $tournaments_json[$key_tournament]['status'] = $tournament_status;
            }

            return response()->json(['code' => 200, 'message' => ['Get Tournament success.'], 'tournaments' => $tournaments_json]);
        } else {
            $tournaments = $tournaments->map(function($tournament, $key) {
                if ($tournament->type == 1) {
                    $tournament->type = 'Single Elimination';
                } else if ($tournament->type == 2) {
                    $tournament->type = 'Double Elimination';
                }

                return $tournament;
            });

            return view('participant.tournament', compact('cities', 'tournaments', 'name', 'status', 'order', 'price', 'start_date', 'selected_city'));
        }
    }

    public function show($id, Request $request)
    {
        $tournament = Tournament::with([
                'owner' => function($owner) {
                    $owner->select('id', 'name');
                },
                'city' => function($city) {
                    $city->select('id', 'name');
                }
            ])
            ->withCount([
                'registrations' => function($registrations) {
                    $registrations->whereHas('confirmation', function($confirmation) {
                        $confirmation->whereHas('approval', function($approval) {
                            $approval->where('status', 1);
                        });
                    });
                }
            ])
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->find($id);

        if ($tournament) {
            $tournament->description = str_replace(PHP_EOL, '<br />', $tournament->description);
            // if ($tournament->type == 1) {
            //     $tournament->type = 'Single Elimination';
            // } else if ($tournament->type == 2) {
            //     $tournament->type = 'Double Elimination';
            // }
            $tournament->rules = str_replace(PHP_EOL, '<br />', $tournament->rules);
            $tournament->prize_other = str_replace(PHP_EOL, '<br />', $tournament->prize_other);
            $tournament->matches = $tournament->matches()
                ->select('id', 'tournaments_id', 'scheduled_time', 'round')
                ->with([
                    'participants' => function($participants) {
                        $participants->select('tournaments_registrations.id AS id', 'tournaments_registrations.teams_id AS teams_id', 'matches_participants.matches_result AS matches_result')
                            ->with([
                                'team' => function($team) {
                                    $team->select('id', 'name', 'picture_file_name');
                                }
                            ])
                            ->orderBy('matches_participants.side');
                    }
                ])
                ->get();
            $tournament->max_round = $tournament->matches->max('round');
            $tournament->min_round = $tournament->matches->min('round');
            $tournament->matches = $tournament->matches->groupBy('round');
            $tournament->live_matches = $tournament->matches()
                ->select('id')
                // ->whereHas('dota2_live_matches')
                ->whereHas('dota2_live_matches', function($dota2_live_matches) {
                    $dota2_live_matches->whereHas('dota2_live_match_teams', function($dota2_live_match_teams) {
                        $dota2_live_match_teams->whereNull('matches_result');
                    });
                })
                ->with([
                    'dota2_live_matches' => function($dota2_live_matches) {
                        $dota2_live_matches->select('id', 'matches_id', 'series_type', 'spectators', 'duration')
                            ->with([
                                'dota2_live_match_teams' => function($dota2_live_match_teams) {
                                    $dota2_live_match_teams->select('id', 'dota2_teams_name', 'dota2_teams_logo', 'tournaments_registrations_id', 'dota2_live_matches_id', 'series_wins', 'score', 'side')
                                        ->with([
                                            'tournament_registration' => function($tournament_registration) {
                                                $tournament_registration->select('id', 'teams_id')
                                                    ->with([
                                                        'team' => function($team) {
                                                            $team->select('id', 'name', 'picture_file_name');
                                                        }
                                                    ]);
                                            }
                                        ])
                                        ->where('side', 1)
                                        ->orWhere('side', 2)
                                        ->orderBy('side', 'ASC');
                                }
                            ])
                            ->whereHas('dota2_live_match_teams', function($dota2_live_match_teams) {
                                $dota2_live_match_teams->whereNull('matches_result');
                            });
                    }
                ])
                ->get();

            return view('participant.tournament-detail', compact('tournament'));
        } else {
            abort(404);
        }
    }

    public function getTournamentDetail($id)
    {
        $tournament = Tournament::with([
                'owner' => function($owner) {
                    $owner->select('id', 'name');
                },
                'city' => function($city) {
                    $city->select('id', 'name');
                }
            ])
            ->withCount([
                'registrations' => function($registrations) {
                    $registrations->whereHas('confirmation', function($confirmation) {
                        $confirmation->whereHas('approval', function($approval) {
                            $approval->where('status', 1);
                        });
                    });
                }
            ])
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->find($id);

        if ($tournament) {
            $tournament->matches = $tournament->matches()
                ->select('id', 'tournaments_id', 'scheduled_time', 'round')
                ->with([
                    'participants' => function($participants) {
                        $participants->select('tournaments_registrations.id AS id', 'tournaments_registrations.teams_id AS teams_id', 'matches_participants.side AS side')
                            ->with([
                                'team' => function($team) {
                                    $team->select('id', 'name', 'picture_file_name');
                                }
                            ])
                            ->orderBy('matches_participants.side');
                    }
                ])
                ->get();
            $tournament->max_round = $tournament->matches->max('round');
            $tournament->min_round = $tournament->matches->min('round');
            $tournament->matches = $tournament->matches->groupBy('round');
            $tournament->live_matches = $tournament->matches()
                ->select('id')
                ->whereHas('dota2_live_matches')
                // ->whereHas('dota2_live_matches', function($dota2_live_matches) {
                //     $dota2_live_matches->whereHas('dota2_live_match_teams', function($dota2_live_match_teams) {
                //         $dota2_live_match_teams->whereNull('matches_result');
                //     });
                // })
                ->with([
                    'dota2_live_matches' => function($dota2_live_matches) {
                        $dota2_live_matches->select('id', 'matches_id', 'series_type', 'spectators', 'duration')
                            ->with([
                                'dota2_live_match_teams' => function($dota2_live_match_teams) {
                                    $dota2_live_match_teams->select('id', 'dota2_teams_name', 'dota2_teams_logo', 'tournaments_registrations_id', 'dota2_live_matches_id', 'series_wins', 'score', 'side')
                                        ->with([
                                            'tournament_registration' => function($tournament_registration) {
                                                $tournament_registration->select('id', 'teams_id')
                                                    ->with([
                                                        'team' => function($team) {
                                                            $team->select('id', 'name', 'picture_file_name');
                                                        }
                                                    ]);
                                            }
                                        ])
                                        ->where('side', 1)
                                        ->orWhere('side', 2)
                                        ->orderBy('side', 'ASC');
                                }
                            ]);
                            // ->whereHas('dota2_live_match_teams', function($dota2_live_match_teams) {
                            //     $dota2_live_match_teams->whereNull('matches_result');
                            // });
                    }
                ])
                ->get();

            $tournament_json = [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'description' => $tournament->description,
                'image' => asset('storage/tournament/'.$tournament->logo_file_name),
                'challonges_url' => $tournament->challonges_url,
                'city' => $tournament->city ? $tournament->city->name : '-',
                'address' => $tournament->address ?: '-',
                'max_participant' => $tournament->max_participant,
                'team_size' => $tournament->team_size,
                'rules' => $tournament->rules,
                'prize_1st' => $tournament->prize_1st ?: '-',
                'prize_2nd' => $tournament->prize_2nd ?: '-',
                'prize_3rd' => $tournament->prize_3rd ?: '-',
                'prize_other' => $tournament->prize_other,
                'entry_fee' => $tournament->entry_fee,
                'registration_closed' => strtotime($tournament->registration_closed),
                'start_date' => strtotime($tournament->start_date),
                'end_date' => strtotime($tournament->end_date),
                'number_of_registers' => $tournament->registrations_count
            ];
            if ($tournament->type == 1) {
                $tournament_json['type'] = 'Single Elimination';
            } else if ($tournament->type == 2) {
                $tournament_json['type'] = 'Double Elimination';
            }

            $matches_json = [];
            foreach ($tournament->matches as $round => $matches) {
                if ($round < 0) {
                    $round_alias = 'Lower Round '.abs($round);
                } else if ($round == 0) {
                    $round_alias = 'Bronze Match';
                } else if ($round < $tournament->max_round - 1) {
                    $round_alias = 'Round '.$round;
                } else if ($round == $tournament->max_round - 1) {
                    $round_alias = 'Semifinals';
                } else {
                    $round_alias = 'Finals';
                }

                foreach ($matches as $match) {
                    $player_1 = 'TBD';
                    $player_1_image = asset('img/default-group.png');
                    $player_2 = 'TBD';
                    $player_2_image = asset('img/default-group.png');
                    foreach ($match->participants as $participant) {
                        if ($participant->side == 1) {
                            $player_1 = $participant->team->name;
                            if ($participant->team->picture_file_name) {
                                $player_1_image = asset('storage/team/'.$participant->team->picture_file_name);
                            }
                        } else if ($participant->side == 2) {
                            $player_2 = $participant->team->name;
                            if ($participant->team->picture_file_name) {
                                $player_2_image = asset('storage/team/'.$participant->team->picture_file_name);
                            }
                        }
                    }

                    $matches_json[$round_alias][] = [
                        'id' => $match->id,
                        'player_1' => $player_1,
                        'player_1_image' => $player_1_image,
                        'player_2' => $player_2,
                        'player_2_image' => $player_2_image,
                        'scheduled_date' => strtotime($match->scheduled_time) ?: 0
                    ];
                }
            }

            $live_matches_json = [];
            foreach ($tournament->live_matches as $match) {
                foreach ($match->dota2_live_matches as $live_match) {
                    if ($live_match->series_type == 0) {
                        $series = 'Best of 1';
                    } else if ($live_match->series_type == 1) {
                        $series = 'Best of 3';
                    } else if ($live_match->series_type == 2) {
                        $series = 'Best of 5';
                    }

                    $round = 1;
                    $player_1 = 'TBD';
                    $player_1_image = asset('img/default-group.png');
                    $player_1_score = 0;
                    $player_2 = 'TBD';
                    $player_2_image = asset('img/default-group.png');
                    $player_2_score = 0;
                    foreach ($live_match->dota2_live_match_teams as $participant) {
                        $round = $round + $participant->series_wins;
                        if ($participant->side == 1) {
                            $player_1 = $participant->tournament_registration->team->name;
                            if ($participant->tournament_registration->team->picture_file_name) {
                                $player_1_image = asset('storage/team/'.$participant->tournament_registration->team->picture_file_name);
                            }
                            $player_1_score = $participant->score;
                        } else if ($participant->side == 2) {
                            $player_2 = $participant->tournament_registration->team->name;
                            if ($participant->tournament_registration->team->picture_file_name) {
                                $player_2_image = asset('storage/team/'.$participant->tournament_registration->team->picture_file_name);
                            }
                            $player_2_score = $participant->score;
                        }
                    }

                    $live_matches_json[] = [
                        'id' => $live_match->id,
                        'series' => $series,
                        'round' => $round,
                        'spectators' => $live_match->spectators,
                        'duration' => $live_match->duration,
                        'player_1' => $player_1,
                        'player_1_image' => $player_1_image,
                        'player_1_score' => $player_1_score,
                        'player_2' => $player_2,
                        'player_2_image' => $player_2_image,
                        'player_2_score' => $player_2_score
                    ];
                }
            }

            return response()->json(['code' => 200, 'message' => 'Get Tournament Detail success.', 'tournament' => $tournament_json, 'matches' => $matches_json, 'live_matches_json' => $live_matches_json]);
        } else {
            return response()->json(['code' => 404, 'message' => 'Tournament ID is invalid.']);
        }
    }

    public function registerIndex($id, Request $request)
    {
        $tournament = Tournament::select('id', 'name', 'max_participant', 'team_size', 'registration_closed', 'need_identifications')
            ->withCount([
                'registrations' => function($registrations) {
                    $registrations->whereHas('confirmation', function($confirmation) {
                        $confirmation->whereHas('approval', function($approval) {
                            $approval->where('status', 1);
                        });
                    });
                }
            ])
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->find($id);

        if ($tournament) {
            if ($tournament->registration_closed >= date('Y-m-d H:i:s')) {
                if ($tournament->registrations_count < $tournament->max_participant) {
                    // Continue
                } else {
                    abort(404);
                }
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        $member = $request->input('participant_model');
        $teams = Team::getListsForTournaments($member->id, $tournament->id, (boolean) $tournament->need_identifications);

        return view('participant.tournament-register', compact('tournament', 'teams'));
    }

    public function registerAPIIndex($id, Request $request)
    {
        $tournament = Tournament::select('id', 'name', 'max_participant', 'team_size', 'registration_closed', 'need_identifications')
            ->withCount([
                'registrations' => function($registrations) {
                    $registrations->whereHas('confirmation', function($confirmation) {
                        $confirmation->whereHas('approval', function($approval) {
                            $approval->where('status', 1);
                        });
                    });
                }
            ])
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->find($id);

        if ($tournament) {
            if ($tournament->registration_closed >= date('Y-m-d H:i:s')) {
                if ($tournament->registrations_count < $tournament->max_participant) {
                    // Continue
                } else {
                    return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }

        $member = $request->user();
        $teams = Team::getListsForTournaments($member->id, $tournament->id, (boolean) $tournament->need_identifications);

        $tournament_json = [
            'id' => $tournament->id,
            'name' => $tournament->name,
            'team_size' => $tournament->team_size
        ];
        $teams_json = [];
        foreach ($teams as $team) {
            $teams_json[] = [
                'id' => $team->id,
                'name' => $team->name,
                'image' => $team->picture_file_name ? asset('storage/member/'.$team->picture_file_name) : asset('img/default-profile.jpg'),
                'number_of_valid_members' => $team->details_count
            ];
        }

        return response()->json(['code' => 200, 'message' => ['Can Register this Tournament'], 'tournament' => $tournament_json, 'teams' => $teams_json]);
    }

    public function register($id, Request $request)
    {
        $tournament = Tournament::select('*')
            ->withCount([
                'registrations' => function($registrations) {
                    $registrations->whereHas('confirmation', function($confirmation) {
                        $confirmation->whereHas('approval', function($approval) {
                            $approval->where('status', 1);
                        });
                    });
                }
            ])
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->find($id);

        if ($tournament) {
            if ($tournament->registration_closed >= date('Y-m-d H:i:s')) {
                if ($tournament->registrations_count < $tournament->max_participant) {
                    // Continue
                } else {
                    abort(404);
                }
            } else {
                return response()->json(['code' => 400, 'message' => ['Tournament Registration already closed.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }

        $member = $request->user();

        $data = [
            'team' => $request->input('team'),
            'members' => $request->input('members')
        ];
        if (!$validatorResponse = ValidatorHelper::validateTournamentRegisterRequest($data, $member->id, $tournament->id, (boolean) $tournament->need_identifications, $tournament->team_size)) {
            DB::beginTransaction();
            try {
                $team = Team::find($data['team']);

                $tournament_registration = new TournamentRegistration();
                $tournament_registration->tournament()->associate($tournament);
                $tournament_registration->team()->associate($team);
                $tournament_registration->save();

                foreach ($data['members'] as $member_id) {
                    $member = Member::select('id', 'steam32_id')->find($member_id);
                    $tournament_registration->members()->attach($member_id, [
                        'steam32_id' => $member->steam32_id,
                        'identification_file_name' => $tournament->need_identifications ? $member->identifications()->orderBy('created_at', 'DESC')->select('identification_file_name')->first()->identification_file_name : null,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }

                DB::commit();
                return response()->json(['code' => 201, 'message' => ['Your team "'.$team->name.'" success joining tournament "'.$tournament->name.'"'], 'url' => url('tournament/confirm-payment/'.$tournament_registration->id)]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => [$e->getMessage()]]);
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function confirmPaymentIndex($id, Request $request)
    {
        $tournament_registration = TournamentRegistration::select('*')
            ->whereDoesntHave('confirmation', function($confirmation) {
                $confirmation->whereHas('approval', function($approval) {
                    $approval->where('status', 1);
                });
            })
            ->find($id);
        if ($tournament_registration) {
            $tournament_registration->load([
                'tournament' => function($tournament) {
                    $tournament->select('id', 'name', 'entry_fee');
                },
                'team' => function($team) {
                    $team->select('id', 'name');
                },
                'members' => function($members) {
                    $members->select('id', 'name')
                        ->orderBy('name');
                },
                'confirmation' => function($confirmation) {
                    $confirmation->select('tournaments_registrations_id', 'name', 'banks_id', 'confirmation_file_name');
                }
            ]);

            $member = $request->input('participant_model');

            $team_leader = $tournament_registration->team->details()->withPivot('members_privilege')->find($member->id);
            if ($team_leader) {
                if ($team_leader->pivot->members_privilege == 2) {
                    return view('participant.tournament-confirm-payment', compact('tournament_registration'));
                } else {
                    abort(404);
                }
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }

    public function confirmPaymentAPIIndex($id, Request $request)
    {
        $tournament_registration = TournamentRegistration::select('*')
            ->whereDoesntHave('confirmation', function($confirmation) {
                $confirmation->whereHas('approval', function($approval) {
                    $approval->where('status', 1);
                });
            })
            ->find($id);
        if ($tournament_registration) {
            $tournament_registration->load([
                'tournament' => function($tournament) {
                    $tournament->select('id', 'name', 'entry_fee');
                },
                'team' => function($team) {
                    $team->select('id', 'name');
                },
                'members' => function($members) {
                    $members->select('id', 'name')
                        ->orderBy('name');
                },
                'confirmation' => function($confirmation) {
                    $confirmation->select('tournaments_registrations_id', 'name', 'banks_id', 'confirmation_file_name');
                }
            ]);

            $member = $request->user();

            $team_leader = $tournament_registration->team->details()->withPivot('members_privilege')->find($member->id);
            if ($team_leader) {
                if ($team_leader->pivot->members_privilege == 2) {
                    $banks_json = [
                        [
                            'id' => 1,
                            'name' => 'BCA'
                        ],
                        [
                            'id' => 2,
                            'name' => 'BRI'
                        ]
                    ];
                    $tournament_registration_json = [
                        'id' => $tournament_registration->id,
                        'register_at' => strtotime($tournament_registration->created_at)
                    ];
                    $tournament_json = [
                        'id' => $tournament_registration->tournament->id,
                        'name' => $tournament_registration->tournament->name,
                        'entry_fee' => $tournament_registration->tournament->entry_fee
                    ];
                    $team_json = [
                        'id' => $tournament_registration->team->id,
                        'name' => $tournament_registration->team->name
                    ];
                    $members_json = [];
                    foreach ($tournament_registration->members as $member) {
                        $members_json[] = [
                            'id' => $member->id,
                            'name' => $member->name
                        ];
                    }
                    $tournament_registration_confirmation_json = [
                        'name' => '',
                        'bank' => 0,
                        'image' => ''
                    ];
                    if ($tournament_registration->confirmation) {
                        $tournament_registration_confirmation_json = [
                            'name' => $tournament_registration->confirmation->name,
                            'bank' => $tournament_registration->confirmation->banks_id,
                            'image' => asset('storage/tournament/confirmation/'.$tournament_registration->confirmation->confirmation_file_name)
                        ];
                    }

                    return response()->json(['code' => 200, 'message' => ['Can Confirm this Tournament Registration.'], 'banks' => $banks_json, 'tournament_registration' => $tournament_registration_json, 'tournament' => $tournament_json, 'team' => $team_json, 'members' => $members_json, 'tournament_registration_confirmation' => $tournament_registration_confirmation_json]);
                } else {
                    return response()->json(['code' => 404, 'message' => ['Tournament Registration ID is invalid.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['Tournament Registration ID is invalid.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament Registration ID is invalid.']]);
        }
    }

    public function confirmPayment($id, Request $request)
    {
        $tournament_registration = TournamentRegistration::select('*')
            ->whereDoesntHave('confirmation', function($confirmation) {
                $confirmation->whereHas('approval', function($approval) {
                    $approval->where('status', 1);
                });
            })
            ->find($id);
        if ($tournament_registration) {
            $tournament_registration->load([
                'team' => function($team) {
                    $team->select('id', 'name');
                }
            ]);

            $member = $request->user();

            $team_leader = $tournament_registration->team->details()->withPivot('members_privilege')->find($member->id);
            if ($team_leader) {
                if ($team_leader->pivot->members_privilege == 2) {
                    // Continue
                } else {
                    return response()->json(['code' => 403, 'message' => ['You are not a team leader of this team.']]);
                }
            } else {
                return response()->json(['code' => 403, 'message' => ['You are not a part of this team.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament Registration ID is invalid.']]);
        }

        $data = [
            'name' => $request->input('name'),
            'bank' => $request->input('bank'),
            'confirmation_file_name' => $request->file('confirmation_file_name')
        ];
        if (!$validatorResponse = ValidatorHelper::validateTournamentRegisterConfirmationRequest($data)) {
            DB::beginTransaction();
            try {
                $path = $data['confirmation_file_name']->storeAs('public/tournament/confirmation', time().uniqid().$data['confirmation_file_name']->hashName());

                $tournament_registration_confirmation = $tournament_registration->confirmation;
                if ($tournament_registration_confirmation) {
                    if ($tournament_registration_confirmation->confirmation_file_name) {
                        Storage::delete('public/tournament/confirmation/'.$tournament_registration_confirmation->confirmation_file_name);
                    }

                    $tournament_registration_confirmation->name = $data['name'];
                    $tournament_registration_confirmation->banks_id = $data['bank'];
                    $tournament_registration_confirmation->confirmation_file_name = substr($path, strlen('public/tournament/confirmation') + 1);
                    $tournament_registration_confirmation->save();

                    $tournament_registration_confirmation->approval()->delete();
                } else {
                    $tournament_registration_confirmation = $tournament_registration->confirmation()->create([
                        'name' => $data['name'],
                        'banks_id' => $data['bank'],
                        'confirmation_file_name' => substr($path, strlen('public/tournament/confirmation') + 1)
                    ]);
                }

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Payment Confirmation Information has been updated.'], 'image_url' => asset('storage/tournament/confirmation/'.$tournament_registration_confirmation->confirmation_file_name)]);
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
}
