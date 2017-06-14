<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\GuzzleHelper;
use App\Tournament;
use App\TournamentApproval;
use App\TournamentRegistrationConfirmation;
use DB;
use Illuminate\Http\Request;
use PHPQRCode\QRcode;
use Storage;

class TournamentController extends BaseController
{
    public function detail($id)
    {
        $tournament = Tournament::select('*')
            ->with('city')
            ->doesntHave('approval')
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
            $tournament->need_identifications = $tournament->need_identifications ? 'Yes' : 'No';

            return view('admin.verify-tournament-detail', compact('tournament'));
        } else {
            abort(404);
        }
    }

    public function approve($id, Request $request)
    {
        $member = $request->user();
        $tournament = Tournament::doesntHave('approval')->find($id);
        if ($tournament) {
            DB::beginTransaction();
            try {
                $challonge = GuzzleHelper::createTournamentChallonge($tournament);
                if ($challonge) {
                    $tournament->challonges_id = $challonge->tournament->id;
                    $tournament->challonges_url = $challonge->tournament->url;
                    $tournament->save();

                    $tournament_approval = new TournamentApproval(['accepted' => 1]);
                    $tournament_approval->member()->associate($member);
                    $tournament->approval()->save($tournament_approval);
                    
                    DB::commit();
                    return response()->json(['code' => 200, 'message' => ['Decline tournament success.']]);
                } else {
                    DB::rollBack();
                    return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }
    }

    public function decline($id, Request $request)
    {
        $member = $request->user();
        $tournament = Tournament::doesntHave('approval')->find($id);
        if ($tournament) {
            DB::beginTransaction();
            try {
                $tournament_approval = new TournamentApproval(['accepted' => 0]);
                $tournament_approval->member()->associate($member);
                $tournament->approval()->save($tournament_approval);
                
                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Decline tournament success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament ID is invalid.']]);
        }
    }

    public function verifyTournamentPaymentIndex()
    {
        $tournament_registration_confirmations = TournamentRegistrationConfirmation::select('*')
            ->with([
                'registration' => function($registration) {
                    $registration->select('id', 'tournaments_id', 'teams_id')
                        ->with([
                            'tournament' => function($tournament) {
                                $tournament->select('id', 'name', 'entry_fee');
                            },
                            'team' => function($team) {
                                $team->select('id', 'name');
                            }
                        ]);
                }
            ])
            ->whereDoesntHave('approval', function($approval) {
                $approval->where('tournaments_registrations_confirmations_approvals.status', 1);
            })
            ->get();

        return view('admin.verify-tournament-payment', compact('tournament_registration_confirmations'));
    }

    public function approvePayment($id, Request $request)
    {
        $tournament_registration_confirmation = TournamentRegistrationConfirmation::whereDoesntHave('approval')->find($id);
        if ($tournament_registration_confirmation) {
            // Continue
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament Registration Confirmation ID is invalid.']]);
        }

        DB::beginTransaction();
        try {
            $tournament_registration = $tournament_registration_confirmation->registration;
            $tournament = $tournament_registration->tournament;
            $team = $tournament_registration->team;
            $qr_identifiers = [];
            if ($tournament->need_identifications) {
                $members = $tournament_registration->members;
                $qr_pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                foreach ($members as $member) {
                    $qr_identifier = substr(str_shuffle(str_repeat($qr_pool, 5)), 0, 20);
                    $tournament_registration->members()->updateExistingPivot($member->id, [
                        'qr_identifier' => $qr_identifier
                    ]);

                    $qr_data = base64_encode($qr_identifier);
                    $qr_file_name = md5($qr_identifier);
                    QRcode::png($qr_data, storage_path('app/public/tournament/qr/').$qr_file_name.'.png', 'H', 4);
                    array_push($qr_identifiers, $qr_file_name);
                }
            }
            $challonge_participant = GuzzleHelper::createTournamentChallongeParticipant($tournament, $team);
            if ($challonge_participant) {
                $tournament_registration->challonges_participants_id = $challonge_participant->participant->id;
                $tournament_registration->save();

                $tournament_registration_confirmation_approval = $tournament_registration_confirmation->approval()->create([
                    'status' => 1
                ]);

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Approve tournament payment success.']]);
            } else {
                DB::rollBack();
                foreach ($qr_identifiers as $qr_identifier) {
                    Storage::delete('public/tournament/qr/'.$qr_identifier.'.png');
                }
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($qr_identifiers as $qr_identifier) {
                Storage::delete('public/tournament/qr/'.$qr_identifier.'.png');
            }
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function declinePayment($id, Request $request)
    {
        $tournament_registration_confirmation = TournamentRegistrationConfirmation::whereDoesntHave('approval')->find($id);
        if ($tournament_registration_confirmation) {
            // Continue
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament Registration Confirmation ID is invalid.']]);
        }

        DB::beginTransaction();
        try {
            $tournament_registration_confirmation_approval = $tournament_registration_confirmation->approval()->create([
                'status' => 0
            ]);

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['Approve tournament payment success.']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }
}
