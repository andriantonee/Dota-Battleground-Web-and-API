<?php

namespace App\Http\Controllers\Organizer;

use App\City;
use App\Helpers\GuzzleHelper;
use App\Helpers\ValidatorHelper;
use App\Match;
use App\Tournament;
use Carbon;
use DB;
use Illuminate\Http\Request;

class TournamentController extends BaseController
{
    public function index(Request $request)
    {
        $status = $request->input('status') ?: 1;

        $organizer = $request->input('organizer_model');
        $tournaments = Tournament::select('id', 'name', 'logo_file_name', 'challonges_url', 'max_participant', 'type', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'start', 'complete', 'cancel', 'created_at')
            ->withCount([
                'registrations' => function($registrations) {
                    $registrations->whereHas('confirmation', function($confirmation) {
                        $confirmation->whereHas('approval', function($approval) {
                            $approval->where('status', 1);
                        });
                    });
                }
            ])
            ->where('members_id', $organizer->id)
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            });

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

        $tournaments = $tournaments->get();

        $tournaments = $tournaments->map(function($tournament, $key) {
            if ($tournament->type == 1) {
                $tournament->type = 'Single Elimination';
            } else if ($tournament->type == 2) {
                $tournament->type = 'Double Elimination';
            }

            return $tournament;
        });

        return view('organizer.tournament', compact('tournaments', 'status'));
    }

    public function getMyTournament(Request $request)
    {
        $organizer = $request->user();
        $tournaments = Tournament::select('id', 'name', 'logo_file_name', 'type', 'cities_id', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'start', 'complete', 'cancel', 'members_id')
            ->where('members_id', $organizer->id)
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->where('start', 1)
            ->where('complete', 0)
            ->get();

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
            if ($tournament->cancel == 0) {
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
            } else if ($tournament->cancel == 1) {
                $tournament_status = 'Cancel';
            }
            $tournaments_json[$key_tournament]['status'] = $tournament_status;
        }

        return response()->json(['code' => 200, 'message' => ['Get Tournament success.'], 'tournaments' => $tournaments_json]);
    }

    public function getMyTournamentDetail($id, Request $request)
    {
        $organizer = $request->user();
        $tournament = Tournament::select('id')
            ->where('members_id', $organizer->id)
            ->whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->where('start', 1)
            ->where('complete', 0)
            ->find($id);
        if ($tournament) {
            $max_round = $tournament->matches()->max('round');
            $matches = $tournament->matches()
                ->select('id', 'tournaments_id', 'scheduled_time', 'round')
                ->with([
                    'participants' => function($participants) {
                        $participants->select('tournaments_registrations.id AS id', 'tournaments_registrations.teams_id AS teams_id', 'matches_participants.side')
                            ->with([
                                'team' => function($team) {
                                    $team->select('id', 'name', 'picture_file_name');
                                }
                            ]);
                    }
                ])
                ->whereHas('participants', function($participants) {
                    $participants->select('matches_participants.matches_id AS matches_id')
                        ->whereNull('matches_participants.matches_result')
                        ->where(function($side) {
                            $side->where('matches_participants.side', 1)
                                ->orWhere('matches_participants.side', 2);
                        })
                        ->groupBy('matches_participants.matches_id')
                        ->havingRaw('COUNT(*) = 2');
                })
                ->get();

            $matches_json = [];
            foreach ($matches as $key_match => $match) {
                if ($match->round < 0) {
                    $round = 'Lower Round '.abs($match->round);
                } else if ($match->round == 0) {
                    $round = 'Bronze Match';
                } else if ($match->round < $max_round - 1) {
                    $round = 'Round '.$match->round;
                } else if ($match->round == $max_round - 1) {
                    $round = 'Semifinals';
                } else {
                    $round = 'Finals';
                }

                $player_1_id = 0;
                $player_1 = 'TBD';
                $player_1_image = asset('img/default-group.png');
                $player_2_id = 0;
                $player_2 = 'TBD';
                $player_2_image = asset('img/default-group.png');
                foreach ($match->participants as $participant) {
                    if ($participant->side == 1) {
                        $player_1_id = $participant->id;
                        $player_1 = $participant->team->name;
                        if ($participant->team->picture_file_name) {
                            $player_1_image = asset('storage/team/'.$participant->team->picture_file_name);
                        }
                    } else if ($participant->side == 2) {
                        $player_2_id = $participant->id;
                        $player_2 = $participant->team->name;
                        if ($participant->team->picture_file_name) {
                            $player_2_image = asset('storage/team/'.$participant->team->picture_file_name);
                        }
                    }
                }

                $matches_json[$round][] = [
                    'id' => $match->id,
                    'player_1_id' => $player_1_id,
                    'player_1' => $player_1,
                    'player_1_image' => $player_1_image,
                    'player_2_id' => $player_2_id,
                    'player_2' => $player_2,
                    'player_2_image' => $player_2_image,
                    'scheduled_date' => strtotime($match->scheduled_time) ?: 0
                ];
            }

            return response()->json(['code' => 200, 'message' => ['Get Tournament Detail success.'], 'matches' => $matches_json]);
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }
    }

    public function create()
    {
        $cities = City::select('id', 'name')->get();

        return view('organizer.tournament-create', compact('cities'));
    }

    public function store(Request $request)
    {
        $dataRequest = $request->all();
        $member = $request->user();

        $data = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'logo' => $request->file('logo'),
            'type' => $request->input('type'),
            'league_id' => $request->input('league_id'),
            'address' => $request->input('address'),
            'max_participant' => $request->input('max_participant'),
            'team_size' => $request->input('team_size'),
            'rules' => $request->input('rules'),
            'prize_1st' => $request->input('prize_1st'),
            'prize_2nd' => $request->input('prize_2nd'),
            'prize_3rd' => $request->input('prize_3rd'),
            'prize_other' => $request->input('prize_other'),
            'entry_fee' => $request->input('entry_fee'),
            'registration_closed' => $request->input('registration_closed'),
            'upload_identification_card' => 1,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date')
        ];
        if ($request->input('city')) {
            $data['city'] = $request->input('city');
        }
        // if (array_key_exists('upload_identification_card', $dataRequest)) {
        //     $data['upload_identification_card'] = $request->input('upload_identification_card');
        // }

        if (!$validatorResponse = ValidatorHelper::validateTournamentCreateRequest($data)) {
            DB::beginTransaction();
            try {
                $path = $data['logo']->storeAs('public/tournament', time().uniqid().$data['logo']->hashName());
                $tournament = new Tournament([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'logo_file_name' => substr($path, strlen('public/tournament') + 1),
                    'type' => $data['type'],
                    'leagues_id' => $data['league_id'] ?: null,
                    'address' => $data['address'] ?: null,
                    'max_participant' => $data['max_participant'],
                    'team_size' => $data['team_size'],
                    'rules' => $data['rules'],
                    'prize_1st' => $data['prize_1st'] ?: null,
                    'prize_2nd' => $data['prize_2nd'] ?: null,
                    'prize_3rd' => $data['prize_3rd'] ?: null,
                    'prize_other' => $data['prize_other'] ?: null,
                    'entry_fee' => $data['entry_fee'],
                    'registration_closed' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $data['registration_closed']))),
                    // 'need_identifications' => array_key_exists('upload_identification_card', $data) ? $data['upload_identification_card'] : 0,
                    'need_identifications' => $data['upload_identification_card'],
                    'start_date' => date('Y-m-d', strtotime(str_replace('/', '-', $data['start_date']))),
                    'end_date' => date('Y-m-d', strtotime(str_replace('/', '-', $data['end_date'])))
                ]);
                if (array_key_exists('city', $data)) {
                    $city = City::find($data['city']);
                    $tournament->city()->associate($city);
                }
                $tournament->owner()->associate($member);
                $tournament->save();

                DB::commit();
                return response()->json(['code' => 201, 'message' => ['Tournament has been created.'], 'redirect_url' => url('/organizer/tournament/'.$tournament->id.'/detail')]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function detail($id, Request $request)
    {
        $tournament = Tournament::find($id);
        $organizer = $request->input('organizer_model');
        if ($tournament) {
            if ($tournament->owner()->find($organizer->id)) {
                $tournament->load([
                    'approval',
                    'registrations' => function($registrations) {
                        $registrations->with([
                                'team' => function($team) {
                                    $team->select('id', 'name', 'picture_file_name');
                                },
                                'members' => function($members) {
                                    $members->select('members.id', 'members.name', 'members.picture_file_name', 'tournaments_registrations_details.identification_file_name');
                                },
                            ])
                            ->whereHas('confirmation', function($confirmation) {
                                $confirmation->whereHas('approval', function($approval) {
                                    $approval->where('status', 1);
                                });
                            });
                    }
                ]);
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
                $tournament->available_matches_report = $tournament->matches()
                    ->select('id', 'tournaments_id', 'round')
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
                    ->whereHas('participants', function($participants) {
                        $participants->select('matches_participants.matches_id AS matches_id')
                            ->whereNull('matches_participants.matches_result')
                            ->where(function($side) {
                                $side->where('matches_participants.side', 1)
                                    ->orWhere('matches_participants.side', 2);
                            })
                            ->groupBy('matches_participants.matches_id')
                            ->havingRaw('COUNT(*) = 2');
                    })
                    ->get();
                $tournament->available_matches_report_max_round = $tournament->available_matches_report->max('round');
                $tournament->available_matches_report_min_round = $tournament->available_matches_report->min('round');
                $tournament->available_matches_report = $tournament->available_matches_report->groupBy('round');
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
                $cities = City::select('id', 'name')->get();

                return view('organizer.tournament-detail', compact('tournament', 'cities'));
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }

    public function update($id, Request $request)
    {
        $tournament = Tournament::find($id);
        $organizer = $request->user();
        if ($tournament) {
            if ($tournament->owner()->find($organizer->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }

        $dataRequest = $request->all();
        $data = [
            'description' => $request->input('description'),
            'league_id' => $request->input('league_id'),
            'address' => $request->input('address'),
            'prize_1st' => $request->input('prize_1st'),
            'prize_2nd' => $request->input('prize_2nd'),
            'prize_3rd' => $request->input('prize_3rd'),
            'prize_other' => $request->input('prize_other')
        ];
        if ($request->input('city')) {
            $data['city'] = $request->input('city');
        }

        if (!$validatorResponse = ValidatorHelper::validateTournamentUpdateRequest($data, $id)) {
            DB::beginTransaction();
            try {
                $tournament->description = $data['description'];
                if (!$tournament->start) {
                    $tournament->leagues_id = $data['league_id'] ?: null;
                }
                $tournament->address = $data['address'] ?: null;
                $tournament->prize_1st = $data['prize_1st'] ?: null;
                $tournament->prize_2nd = $data['prize_2nd'] ?: null;
                $tournament->prize_3rd = $data['prize_3rd'] ?: null;
                $tournament->prize_other = $data['prize_other'] ?: null;
                if (array_key_exists('city', $data)) {
                    $city = City::find($data['city']);
                    $tournament->city()->associate($city);
                } else {
                    $tournament->city()->dissociate();
                }
                $tournament->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Tournament has been updated.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function updateType($id, Request $request)
    {
        $tournament = Tournament::find($id);
        $organizer = $request->user();
        if ($tournament) {
            if ($tournament->owner()->find($organizer->id)) {
                if (!$tournament->start) {
                    // Continue
                } else {
                    return response()->json(['code' => 400, 'message' => ['Tournament has been started. Cannot update tournament type anymore.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }

        $data = [
            'type' => $request->input('type'),
            'randomize' => $request->input('randomize') ?: 0
        ];

        if (!$validatorResponse = ValidatorHelper::validateTournamentTypeUpdateRequest($data, $id)) {
            DB::beginTransaction();
            try {
                if ($tournament->type != $data['type']) {
                    $challonge_type = GuzzleHelper::updateTournamentChallongeType($tournament, $data['type']);
                    if ($challonge_type) {
                        $tournament->type = $data['type'];
                        $tournament->save();
                    } else {
                        return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }

            if ($data['randomize'] == 1) {
                $challonge_seed = GuzzleHelper::updateTournamentChallongeParticipantSeed($tournament);
                if ($challonge_seed) {
                    // Continue
                } else {
                    return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
                }
            }

            return response()->json(['code' => 200, 'message' => ['Tournament Bracket has been updated.']]);
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function start($id, Request $request)
    {
        set_time_limit(0);

        $tournament = Tournament::whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->find($id);
        $organizer = $request->user();
        if ($tournament) {
            if ($tournament->owner()->find($organizer->id)) {
                if (date('Y-m-d H:i:s') > $tournament->registration_closed) {
                    if (!$tournament->start) {
                        // Continue
                    } else {
                        return response()->json(['code' => 400, 'message' => ['Tournament already started. Cannot start it anymore.']]);
                    }
                } else {
                    return response()->json(['code' => 400, 'message' => ['Tournament Registration is still open.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }

        $participants = $tournament->registrations()->whereHas('confirmation', function($confirmation) {
                $confirmation->whereHas('approval', function($approval) {
                    $approval->where('status', 1);
                });
            })
            ->get()
            ->keyBy('challonges_participants_id');
        if (count($participants) < 2) {
            return response()->json(['code' => 400, 'message' => ['Tournament Participant does not meet the minimum participant requirement.']]);
        }

        DB::beginTransaction();
        try {
            $challonge_match = GuzzleHelper::startTournamentChallonge($tournament);
            if ($challonge_match) {
                $matches = [];
                foreach ($challonge_match->tournament->matches as $content) {
                    if (!$content->match->optional) {
                        $match = new Match([
                            'challonges_match_id' => $content->match->id,
                            'round' => $content->match->round
                        ]);
                        $tournament->matches()->save($match);
                        $matches[$content->match->id] = $match;

                        if ($content->match->player1_id) {
                            $match->participants()->attach($participants[$content->match->player1_id]->id, [
                                'side' => 1,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        }
                        if ($content->match->player2_id) {
                            $match->participants()->attach($participants[$content->match->player2_id]->id, [
                                'side' => 2,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        }

                        if ($content->match->player1_prereq_match_id) {
                            $match->childs()->attach($matches[$content->match->player1_prereq_match_id]->id, [
                                'from_child_matches_result' => $content->match->player1_is_prereq_match_loser ? 1 : 3,
                                'side' => 1,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        }
                        if ($content->match->player2_prereq_match_id) {
                            $match->childs()->attach($matches[$content->match->player2_prereq_match_id]->id, [
                                'from_child_matches_result' => $content->match->player2_is_prereq_match_loser ? 1 : 3,
                                'side' => 2,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        }
                    }
                }

                $tournament->start = 1;
                $tournament->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Tournament has been started.']]);
            } else {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function end($id, Request $request)
    {
        $tournament = Tournament::whereHas('approval', function($approval) {
                $approval->where('tournaments_approvals.accepted', 1);
            })
            ->find($id);
        $organizer = $request->user();
        if ($tournament) {
            if ($tournament->owner()->find($organizer->id)) {
                if (date('Y-m-d H:i:s') > $tournament->registration_closed) {
                    if (!$tournament->start) {
                        // Continue
                    } else {
                        return response()->json(['code' => 400, 'message' => ['Tournament already started. Cannot end it anymore.']]);
                    }
                } else {
                    return response()->json(['code' => 400, 'message' => ['Tournament Registration is still open.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }

        $participants = $tournament->registrations()->whereHas('confirmation', function($confirmation) {
                $confirmation->whereHas('approval', function($approval) {
                    $approval->where('status', 1);
                });
            })
            ->get()
            ->keyBy('challonges_participants_id');
        if (count($participants) >= 2) {
            return response()->json(['code' => 400, 'message' => ['Tournament Participant meet the minimum participant requirement.']]);
        }

        DB::beginTransaction();
        try {
            $tournament->start = 1;
            $tournament->complete = 1;
            $tournament->cancel = 1;
            $tournament->save();

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['Tournament has been ended.']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function finalize($id, Request $request)
    {
        $tournament = Tournament::find($id);
        $organizer = $request->user();
        if ($tournament) {
            if ($tournament->owner()->find($organizer->id)) {
                if ($tournament->start) {
                    if (!$tournament->complete) {
                        // Continue
                    } else {
                        return response()->json(['code' => 400, 'message' => ['Cannot finalize this Tournament twice.']]);
                    }
                } else {
                    return response()->json(['code' => 400, 'message' => ['Cannot finalize this Tournament unless it is started.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }

        $available_matches_report_count =  $tournament->matches()
            ->whereHas('participants', function($participants) {
                $participants->select('matches_participants.matches_id AS matches_id')
                    ->whereNull('matches_participants.matches_result')
                    ->where(function($side) {
                        $side->where('matches_participants.side', 1)
                            ->orWhere('matches_participants.side', 2);
                    })
                    ->groupBy('matches_participants.matches_id')
                    ->havingRaw('COUNT(*) = 2');
            })
            ->count();
        if ($available_matches_report_count > 0) {
            return response()->json(['code' => 400, 'message' => ['This Tournament have a matches that not reported.']]);
        }

        DB::beginTransaction();
        try {
            if ($success_finalize = GuzzleHelper::finalizeTournamentChallonge($tournament)) {
                $tournament->complete = 1;
                $tournament->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Tournament has been finalize.']]);
            } else {
                DB::rollBack();
                return response()->json(['code' => 404, 'message' => ['Something went wrong. Please try again.']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 404, 'message' => ['Something went wrong. Please try again.']]);
        }
    }
}
