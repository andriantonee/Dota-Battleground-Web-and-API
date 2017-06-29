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
    public function index()
    {
        $cities = City::select('id', 'name')->get();
        $tournaments = Tournament::select('id', 'name', 'logo_file_name', 'type', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'start', 'complete', 'members_id')
            ->with([
                'owner' => function($owner) {
                    $owner->select('id', 'name');
                }
            ])
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

        return view('participant.tournament', compact('cities', 'tournaments'));
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
            if ($tournament->type == 1) {
                $tournament->type = 'Single Elimination';
            } else if ($tournament->type == 2) {
                $tournament->type = 'Double Elimination';
            }
            $tournament->rules = str_replace(PHP_EOL, '<br />', $tournament->rules);
            $tournament->prize_other = str_replace(PHP_EOL, '<br />', $tournament->prize_other);

            return view('participant.tournament-detail', compact('tournament'));
        } else {
            abort(404);
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
        $tournament_registration = TournamentRegistration::find($id);
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

    public function confirmPayment($id, Request $request)
    {
        $tournament_registration = TournamentRegistration::find($id);
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
