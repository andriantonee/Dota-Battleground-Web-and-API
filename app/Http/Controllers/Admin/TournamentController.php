<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\GuzzleHelper;
use App\Tournament;
use App\TournamentApproval;
use App\TournamentRegistrationConfirmation;
use App\TournamentRegistrationConfirmationApproval;
use DB;
use Illuminate\Http\Request;
use PHPQRCode\QRcode;
use Storage;

class TournamentController extends BaseController
{
    public function detail($id)
    {
        $tournament = Tournament::select('*')
            ->with([
                'city',
                'approval' => function($approval) {
                    $approval->select('tournaments_id', 'members_id', 'accepted', 'created_at')
                        ->with([
                            'member' => function($member) {
                                $member->select('id', 'name');
                            }
                        ]);
                }
            ])
            ->withCount('registrations')
            // ->doesntHave('approval')
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

    public function undo($id)
    {
        $tournament = Tournament::select('*')
            ->withCount('registrations')
            ->whereHas('approval')
            ->find($id);
        if ($tournament) {
            if ($tournament->start == 0 && $tournament->registrations_count <= 0) {
                // Continue
            } else {
                return response()->json(['code' => 400, 'message' => ['Cannot Undo this tournament anymore.']]);
            }

            $tournament_approval = $tournament->approval;
            if ($tournament_approval->accepted == 1) {
                $action = 'Accepted';
            } else {
                $action = 'Rejected';
            }

            DB::beginTransaction();
            try {
                if ($tournament_approval->accepted == 1) {
                    $destroy_challonge = GuzzleHelper::destroyTournamentChallonge($tournament);
                    if ($destroy_challonge) {
                        $tournament->challonges_id = null;
                        $tournament->challonges_url = null;
                        $tournament->save();
                    } else {
                        DB::rollBack();
                        return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
                    }
                }

                $tournament_approval->delete();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Undo '.$action.' tournament success.']]);
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
                                $tournament->select('id', 'name', 'max_participant', 'entry_fee', 'start')
                                    ->withCount([
                                        'registrations' => function($registrations) {
                                            $registrations->whereHas('confirmation', function($confirmation) {
                                                $confirmation->whereHas('approval', function($approval) {
                                                    $approval->where('status', 1);
                                                });
                                            });
                                        }
                                    ]);
                            },
                            'team' => function($team) {
                                $team->select('id', 'name');
                            }
                        ]);
                },
                'approval' => function($approval) {
                    $approval->select('tournaments_registrations_confirmations_id', 'members_id', 'status', 'created_at')
                        ->with([
                            'member' => function($member) {
                                $member->select('id', 'name');
                            }
                        ]);
                }
            ])
            // ->whereDoesntHave('approval', function($approval) {
            //     $approval->where('tournaments_registrations_confirmations_approvals.status', 1);
            // })
            ->get();

        return view('admin.verify-tournament-payment', compact('tournament_registration_confirmations'));
    }

    public function approvePayment($id, Request $request)
    {
        $admin = $request->user();
        $tournament_registration_confirmation = TournamentRegistrationConfirmation::whereDoesntHave('approval')->find($id);
        if ($tournament_registration_confirmation) {
            $tournament = $tournament_registration_confirmation->registration->tournament()->select('*')
                ->withCount([
                    'registrations' => function($registrations) {
                        $registrations->whereHas('confirmation', function($confirmation) {
                            $confirmation->whereHas('approval', function($approval) {
                                $approval->where('status', 1);
                            });
                        });
                    }
                ])
                ->first();
            if ($tournament->start == 0) {
                if ($tournament->registrations_count < $tournament->max_participant) {
                    // Continue
                } else {
                    return response()->json(['code' => 400, 'message' => ['Tournament has reach the maximum participant. Cannot approve any confirmation from this tournament anymore.']]);
                }
            } else {
                return response()->json(['code' => 400, 'message' => ['Tournament has been started. Cannot approve any confirmation from this tournament anymore.']]);
            }
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

                $tournament_registration_confirmation_approval = new TournamentRegistrationConfirmationApproval(['status' => 1]);
                $tournament_registration_confirmation_approval->member()->associate($admin);
                $tournament_registration_confirmation->approval()->save($tournament_registration_confirmation_approval);

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
        $admin = $request->user();
        $tournament_registration_confirmation = TournamentRegistrationConfirmation::whereDoesntHave('approval')->find($id);
        if ($tournament_registration_confirmation) {
            // Continue
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament Registration Confirmation ID is invalid.']]);
        }

        DB::beginTransaction();
        try {
            $tournament_registration_confirmation_approval = new TournamentRegistrationConfirmationApproval(['status' => 0]);
            $tournament_registration_confirmation_approval->member()->associate($admin);
            $tournament_registration_confirmation->approval()->save($tournament_registration_confirmation_approval);

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['Approve tournament payment success.']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }

    public function undoPayment($id, Request $request)
    {
        $tournament_registration_confirmation = TournamentRegistrationConfirmation::whereHas('approval')->find($id);
        if ($tournament_registration_confirmation) {
            $tournament_registration = $tournament_registration_confirmation->registration;
            $tournament = $tournament_registration->tournament;
            if ($tournament->start == 0) {
                // Continue
            } else {
                return response()->json(['code' => 400, 'message' => ['Cannot undo this tournament payment anymore.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Tournament Registration Confirmation ID is invalid.']]);
        }

        DB::beginTransaction();
        try {
            $tournament_registration_confirmation_approval = $tournament_registration_confirmation->approval;
            if ($tournament_registration_confirmation_approval->status == 1) {
                $action = 'Accepted';
            } else {
                $action = 'Rejected';
            }

            if ($tournament_registration_confirmation_approval->status == 1) {
                $destroy_challonge_participant = GuzzleHelper::destroyTournamentChallongeParticipant($tournament_registration);
                if ($destroy_challonge_participant) {
                    $tournament_registration->challonges_participants_id = null;
                    $tournament_registration->save();

                    if ($tournament->need_identifications) {
                        $members = $tournament_registration->members()
                            ->select('members.id', 'tournaments_registrations_details.qr_identifier')
                            ->get();
                        foreach ($members as $member) {
                            $qr_file_name = md5($member->qr_identifier);
                            Storage::delete('public/tournament/qr/'.$qr_file_name.'.png');

                            $tournament_registration->members()->updateExistingPivot($member->id, [
                                'qr_identifier' => null
                            ]);
                        }
                    }
                } else {
                    DB::rollBack();
                    return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
                }
            }

            $tournament_registration_confirmation_approval->delete();

            DB::commit();
            return response()->json(['code' => 200, 'message' => ['Undo '.$action.' tournament payment success.']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }
    }
}
