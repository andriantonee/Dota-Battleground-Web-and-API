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
        $organizer = $request->input('organizer_model');
        $tournaments = Tournament::select('id', 'name', 'logo_file_name', 'challonges_url', 'max_participant', 'type', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'created_at')
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
            })
            ->get();

        $tournaments = $tournaments->map(function($tournament, $key) {
            if ($tournament->type == 1) {
                $tournament->type = 'Single Elimination';
            } else if ($tournament->type == 2) {
                $tournament->type = 'Double Elimination';
            }

            return $tournament;
        });

        return view('organizer.tournament', compact('tournaments'));
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
            'rules' => $request->input('rules'),
            'prize_1st' => $request->input('prize_1st'),
            'prize_2nd' => $request->input('prize_2nd'),
            'prize_3rd' => $request->input('prize_3rd'),
            'prize_other' => $request->input('prize_other'),
            'entry_fee' => $request->input('entry_fee'),
            'registration_closed' => $request->input('registration_closed'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date')
        ];
        if ($request->input('city')) {
            $data['city'] = $request->input('city');
        }
        if (array_key_exists('upload_identification_card', $dataRequest)) {
            $data['upload_identification_card'] = $request->input('upload_identification_card');
        }

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
                    'rules' => $data['rules'],
                    'prize_1st' => $data['prize_1st'] ?: null,
                    'prize_2nd' => $data['prize_2nd'] ?: null,
                    'prize_3rd' => $data['prize_3rd'] ?: null,
                    'prize_other' => $data['prize_other'] ?: null,
                    'entry_fee' => $data['entry_fee'],
                    'registration_closed' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $data['registration_closed']))),
                    'need_identifications' => array_key_exists('upload_identification_card', $data) ? $data['upload_identification_card'] : 0,
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
                                    $members->select('id', 'name', 'picture_file_name');
                                },
                            ])
                            ->whereHas('confirmation', function($confirmation) {
                                $confirmation->whereHas('approval', function($approval) {
                                    $approval->where('status', 1);
                                });
                            });
                    },
                    'matches' => function($matches) {
                        $matches->select('id', 'tournaments_id', 'scheduled_time', 'round')
                            ->with([
                                'participants' => function($participants) {
                                    $participants->select('tournaments_registrations.id', 'tournaments_registrations.teams_id')
                                        ->withPivot('side', 'matches_result')
                                        ->with([
                                            'team' => function($team) {
                                                $team->select('id', 'name', 'picture_file_name');
                                            }
                                        ])
                                        ->orderBy('matches_participants.side');
                                }
                            ]);
                    }
                ]);
                $tournament->max_round = $tournament->matches->max('round');
                $tournament->min_round = $tournament->matches->min('round');
                $tournament->matches = $tournament->matches->groupBy('round');
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

            if ($data['type'] == 1) {
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
}
