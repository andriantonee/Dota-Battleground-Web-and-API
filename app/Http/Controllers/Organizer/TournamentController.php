<?php

namespace App\Http\Controllers\Organizer;

use App\City;
use App\Helpers\ValidatorHelper;
use App\Tournament;
use DB;
use Illuminate\Http\Request;

class TournamentController extends BaseController
{
    public function index(Request $request)
    {
        $organizer = $request->input('organizer_model');
        $tournaments = Tournament::select('id', 'name', 'logo_file_name', 'challonges_url', 'max_participant', 'type', 'entry_fee', 'registration_closed', 'start_date', 'end_date', 'created_at')
            ->withCount([
                'registration' => function($registration) {
                    $registration->whereHas('confirmation', function($confirmation) {
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
        $tournament = Tournament::with('approval')->find($id);
        $organizer = $request->input('organizer_model');
        if ($tournament) {
            if ($tournament->owner()->find($organizer->id)) {
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
                $tournament->leagues_id = $data['league_id'] ?: null;
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
            return response()->json(['code' => 400, 'message' => $validatorResponse, 'data' => $data, 'dataRequest' => $dataRequest]);
        }
    }
}
