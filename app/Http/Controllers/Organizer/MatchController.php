<?php

namespace App\Http\Controllers\Organizer;

use App\Helpers\ValidatorHelper;
use App\Helpers\GuzzleHelper;
use App\Match;
use Carbon;
use DB;
use Illuminate\Http\Request;

class MatchController extends BaseController
{
    public function updateSchedule($id, Request $request)
    {
        $match = Match::find($id);
        $organizer = $request->user();
        if ($match) {
            if ($match->tournament->owner()->find($organizer->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
        }

        $data = [
            'scheduled_time' => $request->input('schedule_date_and_time')
        ];

        if (!$validatorResponse = ValidatorHelper::validateMatchScheduleUpdateRequest($data)) {
            DB::beginTransaction();
            try {
                $match->scheduled_time = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $data['scheduled_time'])));
                $match->save();

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Match Scheduled Time has been updated.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function updateScore($id, Request $request)
    {
        $match = Match::select('*')
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
            ->find($id);
        $organizer = $request->user();
        if ($match) {
            if ($match->tournament->owner()->find($organizer->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
        }

        $data = [
            'side_1_score' => $request->input('side_1_score'),
            'side_2_score' => $request->input('side_2_score'),
            'final_score' => $request->input('ckbox_final_score') ?: 0
        ];

        if (!$validatorResponse = ValidatorHelper::validateMatchScoreUpdateRequest($data)) {
            DB::beginTransaction();
            try {
                $side_1_score = $data['side_1_score'];
                $side_2_score = $data['side_2_score'];

                $side_1_matches_result = null;
                $side_2_matches_result = null;
                if ($data['final_score']) {
                    if ($side_1_score > $side_2_score) {
                        $side_1_matches_result = 3;
                        $side_2_matches_result = 1;
                    } else if ($side_2_score > $side_1_score) {
                        $side_1_matches_result = 1;
                        $side_2_matches_result = 3;
                    }
                }

                $side_1_challonges = $match->participants()
                    ->select('tournaments_registrations.id AS id', 'tournaments_registrations.challonges_participants_id AS challonges_participants_id')
                    ->where('matches_participants.side', 1)
                    ->first();
                $side_2_challonges = $match->participants()
                    ->select('tournaments_registrations.id AS id', 'tournaments_registrations.challonges_participants_id AS challonges_participants_id')
                    ->where('matches_participants.side', 2)
                    ->first();

                $success = false;
                if ($match->challonges_match_id) {
                    $match->load([
                        'tournament' => function($tournament) {
                            $tournament->select('id', 'type', 'challonges_id');
                        }
                    ]);
                    $tournament = $match->tournament;
                    if ($tournament) {
                        if ($tournament->challonges_id) {
                            if ($side_1_challonges && $side_2_challonges) {
                                $winner_id = null;
                                if ($side_1_matches_result == 3) {
                                    $winner_id = $side_1_challonges->challonges_participants_id;
                                } else if ($side_2_matches_result == 3) {
                                    $winner_id = $side_2_challonges->challonges_participants_id;
                                }

                                $scores_csv = $side_1_score.'-'.$side_2_score;

                                $success = GuzzleHelper::updateTournamentMatchScore($tournament, $match, $scores_csv, $winner_id);
                            }
                        }
                    }
                } else {
                    $success = true;
                }

                if ($success && $side_1_challonges && $side_2_challonges) {
                    $match->participants()->updateExistingPivot($side_1_challonges->id, [
                        'score' => $side_1_score,
                        'matches_result' => $side_1_matches_result
                    ]);
                    $match->participants()->updateExistingPivot($side_2_challonges->id, [
                        'score' => $side_2_score,
                        'matches_result' => $side_2_matches_result
                    ]);

                    if ($side_1_matches_result && $side_2_matches_result) {
                        $match->load([
                            'parents' => function($parents) {
                                $parents->select('matches.id', 'matches_qualifications_details.from_child_matches_result AS from_child_matches_result', 'matches_qualifications_details.side AS side');
                            }
                        ]);

                        foreach ($match->parents as $parent) {
                            if ($side_1_matches_result == $parent->from_child_matches_result) {
                                $parent->participants()->attach($side_1_challonges->id, [
                                    'side' => $parent->side,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                            } else if ($side_2_matches_result == $parent->from_child_matches_result) {
                                $parent->participants()->attach($side_2_challonges->id, [
                                    'side' => $parent->side,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                            }
                        }
                    }
                }

                DB::commit();
                return response()->json(['code' => 200, 'message' => ['Report Match Success.']]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }
}
