<?php

namespace App\Http\Controllers\Participant;

use App\Dota2LiveMatch;
use App\Dota2LiveMatchComment;
use App\Events\Dota2LiveMatchCommentUpdated;
use App\Helpers\ValidatorHelper;
use DB;
use Illuminate\Http\Request;

class Dota2MatchController extends BaseController
{
    public function show($id, Request $request)
    {
        $dota2_live_match = Dota2LiveMatch::select('id', 'matches_id', 'leagues_id', 'series_type', 'spectators', 'duration', 'roshan_respawn_timer')->find($id);
        if ($dota2_live_match) {
            $dota2_live_match->load([
                'match' => function($match) {
                    $match->select('id', 'tournaments_id')
                        ->with([
                            'tournament' => function($tournament) {
                                $tournament->select('id', 'name', 'logo_file_name');
                            }
                        ]);
                },
            ]);
            $duration = $dota2_live_match->durations()
                ->orderBy('duration', 'ASC')
                ->pluck('duration');

            $radiant = $dota2_live_match->dota2_live_match_teams()
                ->select('id', 'dota2_teams_name', 'tournaments_registrations_id', 'dota2_live_matches_id', 'series_wins', 'score', 'tower_state', 'barracks_state', 'side', 'matches_result')
                ->with([
                    'tournament_registration' => function($tournament_registration) {
                        $tournament_registration->select('id', 'teams_id')
                            ->with([
                                'team' => function($team) {
                                    $team->select('id', 'name', 'picture_file_name');
                                }
                            ]);
                    },
                    'heroes_pick' => function($heroes_pick) {
                        $heroes_pick->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name')
                            ->orderBy('dota2_live_matches_teams_picks.pick_order', 'ASC');
                    },
                    'heroes_ban' => function($heroes_ban) {
                        $heroes_ban->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name')
                            ->orderBy('dota2_live_matches_teams_bans.ban_order', 'ASC');
                    },
                    'dota2_live_match_players' => function($dota2_live_match_players) {
                        $dota2_live_match_players->select('id', 'name', 'members_id', 'dota2_live_matches_teams_id', 'dota2_heroes_id', 'kills', 'death', 'assists', 'last_hits', 'denies', 'gold', 'level', 'gold_per_min', 'xp_per_min', 'respawn_timer', 'position_x', 'position_y', 'net_worth')
                            ->with([
                                'member' => function($member) {
                                    $member->select('id', 'name');
                                },
                                'hero' => function($hero) {
                                    $hero->select('id', 'name', 'picture_file_name');
                                }
                            ])
                            ->orderBy('player_order', 'ASC');
                    }
                ])
                ->where('side', 1)
                ->first();
            $radiant_statistics = [];
            foreach ($radiant->dota2_live_match_players as $dota2_live_match_player) {
                $dota2_live_match_player->abilities = $dota2_live_match_player->abilities()
                    ->select('dota2_abilities.id AS id', 'dota2_abilities.name AS name', 'dota2_abilities.picture_file_name AS picture_file_name', 'dota2_live_matches_players_abilities.ability_order AS ability_order')
                    ->orderBy('dota2_live_matches_players_abilities.ability_order', 'ASC')
                    ->get()
                    ->keyBy('ability_order');
                $dota2_live_match_player->items = $dota2_live_match_player->items()
                    ->select('dota2_items.id AS id', 'dota2_items.name AS name', 'dota2_items.picture_file_name AS picture_file_name', 'dota2_live_matches_players_items.item_order AS item_order')
                    ->orderBy('dota2_live_matches_players_items.item_order', 'ASC')
                    ->get()
                    ->keyBy('item_order');

                $golds = $dota2_live_match_player->golds()
                    ->select('gold_per_min', 'gold', 'net_worth', 'duration')
                    ->orderBy('duration', 'ASC')
                    ->get();
                $xps = $dota2_live_match_player->xps()
                    ->select('dota2_live_matches_players_id', 'xp_per_min', 'xp', 'duration')
                    ->orderBy('duration', 'ASC')
                    ->get();
                $radiant_statistics[] = (object) [
                    'golds' => json_decode($golds->toJson()),
                    'xps' => json_decode($xps->toJson())
                ];
            }

            $dire = $dota2_live_match->dota2_live_match_teams()
                ->select('id', 'dota2_teams_name', 'tournaments_registrations_id', 'dota2_live_matches_id', 'series_wins', 'score', 'tower_state', 'barracks_state', 'side', 'matches_result')
                ->with([
                    'tournament_registration' => function($tournament_registration) {
                        $tournament_registration->select('id', 'teams_id')
                            ->with([
                                'team' => function($team) {
                                    $team->select('id', 'name', 'picture_file_name');
                                }
                            ]);
                    },
                    'heroes_pick' => function($heroes_pick) {
                        $heroes_pick->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name')
                            ->orderBy('dota2_live_matches_teams_picks.pick_order', 'ASC');
                    },
                    'heroes_ban' => function($heroes_ban) {
                        $heroes_ban->select('dota2_heroes.id AS id', 'dota2_heroes.name AS name', 'dota2_heroes.picture_file_name AS picture_file_name')
                            ->orderBy('dota2_live_matches_teams_bans.ban_order', 'ASC');
                    },
                    'dota2_live_match_players' => function($dota2_live_match_players) {
                        $dota2_live_match_players->select('id', 'name', 'members_id', 'dota2_live_matches_teams_id', 'dota2_heroes_id', 'kills', 'death', 'assists', 'last_hits', 'denies', 'gold', 'level', 'gold_per_min', 'xp_per_min', 'respawn_timer', 'position_x', 'position_y', 'net_worth')
                            ->with([
                                'member' => function($member) {
                                    $member->select('id', 'name');
                                },
                                'hero' => function($hero) {
                                    $hero->select('id', 'name', 'picture_file_name');
                                }
                            ])
                            ->orderBy('player_order', 'ASC');
                    }
                ])
                ->where('side', 2)
                ->first();
            $dire_statistics = [];
            foreach ($dire->dota2_live_match_players as $dota2_live_match_player) {
                $dota2_live_match_player->abilities = $dota2_live_match_player->abilities()
                    ->select('dota2_abilities.id AS id', 'dota2_abilities.name AS name', 'dota2_abilities.picture_file_name AS picture_file_name', 'dota2_live_matches_players_abilities.ability_order AS ability_order')
                    ->orderBy('dota2_live_matches_players_abilities.ability_order', 'ASC')
                    ->get()
                    ->keyBy('ability_order');
                $dota2_live_match_player->items = $dota2_live_match_player->items()
                    ->select('dota2_items.id AS id', 'dota2_items.name AS name', 'dota2_items.picture_file_name AS picture_file_name', 'dota2_live_matches_players_items.item_order AS item_order')
                    ->orderBy('dota2_live_matches_players_items.item_order', 'ASC')
                    ->get()
                    ->keyBy('item_order');

                $golds = $dota2_live_match_player->golds()
                    ->select('gold_per_min', 'gold', 'net_worth', 'duration')
                    ->orderBy('duration', 'ASC')
                    ->get();
                $xps = $dota2_live_match_player->xps()
                    ->select('dota2_live_matches_players_id', 'xp_per_min', 'xp', 'duration')
                    ->orderBy('duration', 'ASC')
                    ->get();
                $dire_statistics[] = (object) [
                    'golds' => json_decode($golds->toJson()),
                    'xps' => json_decode($xps->toJson())
                ];
            }

            $dota2_live_match_comments = $dota2_live_match->comments()
                ->with([
                    'member' => function($member) {
                        $member->select('id', 'name', 'picture_file_name');
                    }
                ])
                ->orderBy('created_at', 'DESC')
                ->get();

            return view('participant.dota2-match-detail', compact('dota2_live_match', 'duration', 'radiant', 'dire', 'radiant_statistics', 'dire_statistics', 'dota2_live_match_comments'));
        } else {
            abort(404);
        }
    }

    public function postComment($id, Request $request)
    {
        $dota2_live_match = Dota2LiveMatch::find($id);
        if ($dota2_live_match) {
            $data = [
                'comment' => $request->input('comment')
            ];

            if (!$validatorResponse = ValidatorHelper::validateDota2LiveMatchPostCommentRequest($data)) {
                DB::beginTransaction();
                try {
                    $dota2_live_match_comment = new Dota2LiveMatchComment([
                        'detail' => $data['comment']
                    ]);
                    $dota2_live_match_comment->dota2_live_match()->associate($dota2_live_match);
                    $dota2_live_match_comment->member()->associate($request->user());
                    $dota2_live_match_comment->save();

                    DB::commit();

                    event(new Dota2LiveMatchCommentUpdated($dota2_live_match_comment));

                    return response()->json(['code' => 200, 'message' => ['Comment has been post.']]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
                }
            } else {
                return response()->json(['code' => 400, 'message' => $validatorResponse]);
            }
        } else {
            return response()->json(['code' => 404, 'message' => ['Dota 2 Live Match ID is invalid.']]);
        }
    }
}
