<?php

namespace App\Http\Controllers\Organizer;

use App\Helpers\ValidatorHelper;
use App\Helpers\GuzzleHelper;
use App\Match;
use App\MatchAttendance;
use App\TournamentRegistration;
use Carbon;
use DB;
use Illuminate\Http\Request;

class MatchController extends BaseController
{
    public function getSchedule($id, Request $request)
    {
        $match = Match::find($id);
        $organizer = $request->user();
        if ($match) {
            $tournament = $match->tournament;
            if ($tournament->owner()->find($organizer->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
        }

        $match_childs_count = $match->childs()->count('*');
        if ($match_childs_count > 0) {
            $match_childs_scheduled_set_count = $match->childs()->whereNotNull('scheduled_time')->count('*');
            if ($match_childs_scheduled_set_count > 0) {
                $min_scheduled_time = $match->childs()->max('scheduled_time');
                return response()->json(['code' => 200, 'message' => ['Get Minimum Scheduled Time success.'], 'minDateTime' => date('Y-m-d H:i:s', strtotime($min_scheduled_time))]);
            } else {
                return response()->json(['code' => 200, 'message' => ['Get Minimum Scheduled Time success.'], 'minDateTime' => date('Y-m-d H:i:s', strtotime($tournament->start_date))]);
            }
        } else {
            return response()->json(['code' => 200, 'message' => ['Get Minimum Scheduled Time success.'], 'minDateTime' => date('Y-m-d H:i:s', strtotime($tournament->start_date))]);
        }
    }

    public function updateSchedule($id, Request $request)
    {
        $match = Match::find($id);
        $organizer = $request->user();
        if ($match) {
            $tournament = $match->tournament;
            if ($tournament->owner()->find($organizer->id)) {
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
            $match_parents_count = $match->parents()->count('*');
            if ($match_parents_count > 0) {
                $match_parents_scheduled = $match->parents()->whereNotNull('scheduled_time')->count('*');
                if ($match_parents_scheduled) {
                    return response()->json(['code' => 400, 'message' => ['Match Scheduled Time cannot be changed anymore.']]);
                }
            }

            $match_childs_count = $match->childs()->count('*');
            if ($match_childs_count > 0) {
                $match_childs_scheduled_set_count = $match->childs()->whereNotNull('scheduled_time')->count('*');
                if ($match_childs_count == $match_childs_scheduled_set_count) {
                    $min_scheduled_time = $match->childs()->max('scheduled_time');
                    if ($min_scheduled_time > date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $data['scheduled_time'])))) {
                        return response()->json(['code' => 400, 'message' => ['Match Scheduled Time must be greater or equal '.date('d/m/Y H:i:s', strtotime($min_scheduled_time)).'.']]);
                    }
                } else {
                    return response()->json(['code' => 400, 'message' => ['Match Scheduled Time cannot be set right now.']]);
                }
            } else {
                if (date('Y-m-d H:i:s', strtotime($tournament->start_date)) > date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $data['scheduled_time'])))) {
                    return response()->json(['code' => 400, 'message' => ['Match Scheduled Time must be greater or equal '.date('d/m/Y H:i:s', strtotime($tournament->start_date)).'.']]);
                }
            }

            if (date('Y-m-d 23:59:59', strtotime($tournament->end_date)) < date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $data['scheduled_time'])))) {
                return response()->json(['code' => 400, 'message' => ['Match Scheduled Time must not be greater than '.date('d/m/Y 23:59:59', strtotime($tournament->end_date))]]);
            }

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

    public function getAttendance($id, Request $request)
    {
        $organizer = $request->user();
        $match = Match::find($id);
        if ($match) {
            $tournament = $match->tournament;
            if ($tournament->owner()->find($organizer->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament.']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
        }

        $data = [
            'qr_identifier' => $request->input('qr_identifier')
        ];

        if (!$validatorResponse = ValidatorHelper::validateMatchAttendanceRequest($data)) {
            $tournaments_registration = TournamentRegistration::whereHas('members', function($members) use($data) {
                    $members->where('tournaments_registrations_details.qr_identifier', base64_decode($data['qr_identifier']));
                })
                ->first();
            if ($tournaments_registration) {
                if ($match->participants()->whereIn('matches_participants.side', [1, 2])->find($tournaments_registration->id)) {
                    if (!$match->attendances()->where('qr_identifier', base64_decode($data['qr_identifier']))->exists()) {
                        $team = $tournaments_registration->team()
                            ->select('teams.id AS id', 'teams.name AS name', 'teams.picture_file_name AS picture_file_name')
                            ->first();
                        $member = $tournaments_registration->members()
                            ->select('members.id AS id', 'members.name AS name', 'tournaments_registrations_details.steam32_id AS steam32_id', 'members.picture_file_name AS picture_file_name', 'tournaments_registrations_details.identification_file_name AS identification_file_name')
                            ->where('tournaments_registrations_details.qr_identifier', base64_decode($data['qr_identifier']))
                            ->first();
                        $tournament_json = [
                            'id' => $tournament->id,
                            'name' => $tournament->name
                        ];
                        $tournament_registration_json = [
                            'id' => $tournaments_registration->id,
                            'register_at' => strtotime($tournaments_registration->created_at)
                        ];
                        $team_json = [
                            'id' => $team->id,
                            'name' => $team->name,
                            'image' => $team->picture_file_name ? asset('storage/team/'.$team->picture_file_name) : asset('img/default-group.png')
                        ];
                        $member_json = [
                            'id' => $member->id,
                            'name' => $member->name,
                            'steam32_id' => $member->steam32_id,
                            'image' => $member->picture_file_name ? asset('storage/member/'.$member->picture_file_name) : asset('img/default-profile.jpg'),
                            'identification_image' => asset('storage/member/identification/'.$member->identification_file_name)
                        ];

                        return response()->json(['code' => 200, 'message' => ['QR Code is valid.'], 'tournament' => $tournament_json, 'tournament_registration' => $tournament_registration_json, 'team' => $team_json, 'member' => $member_json]);
                    } else {
                        return response()->json(['code' => 400, 'message' => ['This Participant already mark as attend.']]);
                    }
                } else {
                    return response()->json(['code' => 404, 'message' => ['QR Code is invalid.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['QR Code is invalid.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function postAttendance($id, Request $request)
    {
        $member = $request->user();
        $match = Match::find($id);
        if ($match) {
            $tournament = $match->tournament;
            if ($tournament->owner()->find($member->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
        }

        $data = [
            'qr_identifier' => $request->input('qr_identifier')
        ];

        if (!$validatorResponse = ValidatorHelper::validateMatchAttendanceRequest($data)) {
            $tournaments_registration = TournamentRegistration::whereHas('members', function($members) use($data) {
                    $members->where('tournaments_registrations_details.qr_identifier', base64_decode($data['qr_identifier']));
                })
                ->first();
            if ($match->participants()->whereIn('matches_participants.side', [1, 2])->find($tournaments_registration->id)) {
                $attendance_status = $match->attendances()->where('qr_identifier', base64_decode($data['qr_identifier']))->exists();
                if (!$attendance_status) {
                    $match_attendance = new MatchAttendance(['qr_identifier' => base64_decode($data['qr_identifier'])]);
                    $match->attendances()->save($match_attendance);

                    return response()->json(['code' => 200, 'message' => ['This Participant has been marked as attend.']]);
                } else {
                    return response()->json(['code' => 400, 'message' => ['This Participant already mark as attend.']]);
                }
            } else {
                return response()->json(['code' => 404, 'message' => ['QR Code is invalid.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    public function getMatchTeamAttendance($id, Request $request)
    {
        $member = $request->user();
        $match = Match::find($id);
        if ($match) {
            $tournament = $match->tournament;
            if ($tournament->owner()->find($member->id)) {
                // Continue
            } else {
                return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
        }

        $data = [
            'tournament_registration_id' => $request->input('tournament_registration_id')
        ];

        if (!$validatorResponse = ValidatorHelper::validateGetMatchTeamAttendanceRequest($data)) {
            $qr_identifier_attendances = $match->attendances()->pluck('qr_identifier')->toArray();
            $tournament_registration = $match->participants()
                ->whereIn('matches_participants.side', [1, 2])
                ->find($data['tournament_registration_id']);
            if ($tournament_registration) {
                $team = $tournament_registration->team;
                $members = $tournament_registration->members()
                    ->select('members.id AS id', 'members.name AS name', 'members.picture_file_name AS picture_file_name', 'tournaments_registrations_details.qr_identifier')
                    ->get();

                $tournament_json = [
                    'id' => $tournament->id,
                    'name' => $tournament->name
                ];
                $tournament_registration_json = [
                    'id' => $tournament_registration->id,
                    'register_at' => strtotime($tournament_registration->created_at)
                ];
                $team_json = [
                    'id' => $team->id,
                    'name' => $team->name,
                    'image' => $team->picture_file_name ? asset('storage/team/'.$team->picture_file_name) : asset('img/default-group.png')
                ];
                $members_json = [];
                foreach ($members as $key_member => $member) {
                    $members_json[$key_member] = [
                        'id' => $member->id,
                        'name' => $member->name,
                        'image' => $member->picture_file_name ? asset('storage/member/'.$member->picture_file_name) : asset('img/default-profile.jpg'),
                        'attendances_status' => in_array($member->qr_identifier, $qr_identifier_attendances) ? 1 : 0
                    ];
                }

                return response()->json(['code' => 200, 'message' => ['Get Team Attendances success.'], 'tournament' => $tournament_json, 'tournament_registration' => $tournament_registration_json, 'team' => $team_json, 'members' => $members_json]);
            } else {
                return response()->json(['code' => 400, 'message' => ['Tournament Registration ID is invalid.']]);
            }
        } else {
            return response()->json(['code' => 400, 'message' => $validatorResponse]);
        }
    }

    // public function start($id, Request $request)
    // {
    //     $member = $request->user();
    //     $match = Match::find($id);
    //     if ($match) {
    //         $tournament = $match->tournament;
    //         if ($tournament->owner()->find($member->id)) {
    //             // Continue
    //         } else {
    //             return response()->json(['code' => 404, 'message' => ['Member is not an owner of this Tournament']]);
    //         }
    //     } else {
    //         return response()->json(['code' => 404, 'message' => ['Match ID is invalid.']]);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $player_1 = null;
    //         $player_2 = null;

    //         $qr_identifier_attendances = $match->attendances()->pluck('qr_identifier')->toArray();
    //         $participants = $match->participants()
    //             ->select('tournaments_registrations.id AS id', 'tournaments_registrations.disqualification AS disqualification', 'tournaments_registrations.challonges_participants_id AS challonges_participants_id', 'matches_participants.side AS side')
    //             ->whereIn('matches_participants.side', [1, 2])
    //             ->get();
    //         foreach ($participants as $participant) {
    //             if ($participant->side == 1) {
    //                 $player_1 = $participant;
    //             } else if ($participant->side == 2) {
    //                 $player_2 = $participant;
    //             }

    //             if ($participant->disqualification == 0) {
    //                 $qr_identifier = $participant->members()->pluck('qr_identifier')->toArray();
    //                 $qr_identifier_not_attend = array_diff($qr_identifier, $qr_identifier_attendances);
    //                 if ($qr_identifier_not_attend) {
    //                     $participant->disqualification = 1;
    //                     $participant->save();
    //                 }
    //             }
    //         }

    //         if ($player_1 && $player_2) {
    //             $winner_id = null;
    //             $player_1_score = 0;
    //             $player_2_score = 0;
    //             $player_1_result = 1;
    //             $player_2_result = 1;
    //             if ($player_1->disqualification == 1) {
    //                 $winner_id = $player_2->challonges_participants_id;
    //                 $player_2_score = 1;
    //                 $player_2_result = 3;
    //             } else if ($player_2->disqualification == 1) {
    //                 $winner_id = $player_1->challonges_participants_id;
    //                 $player_1_score = 1;
    //                 $player_1_result = 3;
    //             }

    //             if ($winner_id) {
    //                 if ($tournament->challonges_id) {
    //                     if ($match->challonges_match_id) {
    //                         // $scores_csv = $player_1_score.'-'.$player_2_score;
    //                         // $success = GuzzleHelper::updateTournamentMatchScore($tournament, $match, $scores_csv, $winner_id);
    //                         $success = true;
    //                     } else {
    //                         $success = true;
    //                     }
    //                 } else {
    //                     $success = true;
    //                 }

    //                 if ($success) {
    //                     $match->participants()->updateExistingPivot($player_1->id, [
    //                         'score' => $player_1_score,
    //                         'matches_result' => $player_1_result
    //                     ]);
    //                     $match->participants()->updateExistingPivot($player_2->id, [
    //                         'score' => $player_2_score,
    //                         'matches_result' => $player_2_result
    //                     ]);

    //                     if ($player_1_result && $player_2_result) {
    //                         $match->load([
    //                             'parents' => function($parents) {
    //                                 $parents->select('matches.id', 'matches_qualifications_details.from_child_matches_result AS from_child_matches_result', 'matches_qualifications_details.side AS side');
    //                             }
    //                         ]);

    //                         foreach ($match->parents as $parent) {
    //                             if ($player_1_result == $parent->from_child_matches_result) {
    //                                 $parent->participants()->attach($player_1->id, [
    //                                     'side' => $parent->side,
    //                                     'created_at' => Carbon::now(),
    //                                     'updated_at' => Carbon::now()
    //                                 ]);
    //                             } else if ($player_2_result == $parent->from_child_matches_result) {
    //                                 $parent->participants()->attach($player_2->id, [
    //                                     'side' => $parent->side,
    //                                     'created_at' => Carbon::now(),
    //                                     'updated_at' => Carbon::now()
    //                                 ]);
    //                             }
    //                         }
    //                     }
    //                 }
    //             }

    //             DB::commit();
    //             return response()->json(['code' => 200, 'message' => ['Successfully Start this Match.']]);
    //         } else {
    //             DB::rollBack();
    //             return response()->json(['code' => 400, 'message' => ['This Match cannot Start cause of missing Players.']]);
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['code' => 500, 'message' => [$e->getMessage()]]);
    //         return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
    //     }
    // }
}
