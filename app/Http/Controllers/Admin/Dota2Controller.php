<?php

namespace App\Http\Controllers\Admin;

use App\Dota2Ability;
use App\Dota2Hero;
use App\Dota2Item;
use Illuminate\Http\Request;

use App\Dota2LiveMatch;
use App\Dota2LiveMatchDurationLog;
use App\Dota2LiveMatchTeam;
use App\Dota2LiveMatchPlayer;
use App\Dota2LiveMatchPlayerGold;
use App\Dota2LiveMatchPlayerXP;
use App\Helpers\GuzzleHelper;
use App\Match;
use App\Member;
use App\Tournament;
use App\TournamentRegistration;
use Carbon;

use App\Events\Dota2LiveMatchDirePlayersUpdated;
use App\Events\Dota2LiveMatchPlayersItemsUpdated;
use App\Events\Dota2LiveMatchRadiantPlayersUpdated;
use App\Events\Dota2LiveMatchUpdated;

class Dota2Controller extends BaseController
{
    public function updateAbilities()
    {
        set_time_limit(0);

        try {
            $abilities = VDFParse(storage_path('app/Dota 2/npc_abilities.txt'));
            foreach ($abilities['DOTAAbilities'] as $ability_id => $ability) {
                if (is_array($ability)) {
                    if (array_key_exists('ID', $ability)) {
                        if ($ability['ID'] != '0') {
                            $dota2_ability = Dota2Ability::updateOrCreate([
                                'id' => $ability['ID']
                            ], [
                                'name_id' => $ability_id,
                                'name' => ucwords(strtolower(str_replace('_', ' ', $ability_id))),
                                'picture_file_name' => file_exists(public_path().'/img/dota-2/abilities/'.$ability_id.'.png') ? $ability_id.'.png' : null
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }

        return response()->json(['code' => 200, 'message' => ['Update abilities success.']]);
    }

    public function updateHeroes()
    {
        set_time_limit(0);

        try {
            $dota2_abilities = Dota2Ability::get();
            if (count($dota2_abilities) <= 0) {
                return response()->json(['code' => 400, 'message' => ['Before update heroes. First, you must execute update abilities.']]);
            }
            $dota2_abilities = $dota2_abilities->groupBy('name_id');

            $heroes = VDFParse(storage_path('app/Dota 2/npc_heroes.txt'));
            foreach ($heroes['DOTAHeroes'] as $hero_id => $hero) {
                if (is_array($hero)) {
                    if (array_key_exists('HeroID', $hero)) {
                        if ($hero['HeroID'] != '0' && array_key_exists('url', $hero)) {
                            $dota2_hero = Dota2Hero::updateOrCreate([
                                'id' => $hero['HeroID']
                            ], [
                                'name_id' => $hero_id,
                                'name' => ucwords(strtolower(str_replace('_', ' ', $hero['url']))),
                                'picture_file_name' => file_exists(public_path().'/img/dota-2/heroes/'.substr($hero_id, strlen('npc_dota_hero_')).'.png') ? substr($hero_id, strlen('npc_dota_hero_')).'.png' : null
                            ]);

                            // Delete all skills that already attached to the hero
                            $dota2_hero->abilities()->detach();

                            // Hero Skill
                            $ability_length = 4;
                            $ability_status_true = [1, 2, 3, 4];
                            if ($dota2_hero->id == 3) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 4];
                            } else if ($dota2_hero->id == 10) {
                                $ability_length = 8;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 11) {
                                $ability_length = 6;
                                $ability_status_true = [1, 4, 5, 6];
                            } else if ($dota2_hero->id == 13) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 22) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 23) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 4];
                            } else if ($dota2_hero->id == 38) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 4, 5];
                            } else if ($dota2_hero->id == 46) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 54) {
                                $ability_length = 8;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 66) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 4, 5];
                            } else if ($dota2_hero->id == 67) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 68) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 4];
                            } else if ($dota2_hero->id == 69) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 70) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 72) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 73) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3 ,4];
                            } else if ($dota2_hero->id == 74) {
                                $ability_length = 16;
                                $ability_status_true = [1, 2, 3];
                            } else if ($dota2_hero->id == 79) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 80) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 83) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 84) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 86) {
                                $ability_length = 10;
                                $ability_status_true = [1, 3, 4, 7];
                            } else if ($dota2_hero->id == 88) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 89) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 4];
                            } else if ($dota2_hero->id == 90) {
                                $ability_length = 9;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 91) {
                                $ability_length = 7;
                                $ability_status_true = [1, 3, 4, 7];
                            } else if ($dota2_hero->id == 95) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 4, 5];
                            } else if ($dota2_hero->id == 98) {
                                $ability_length = 7;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 100) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 103) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 105) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 106) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3 ,5];
                            } else if ($dota2_hero->id == 107) {
                                $ability_length = 6;
                                $ability_status_true = [1, 2, 3, 6];
                            } else if ($dota2_hero->id == 108) {
                                $ability_length = 5;
                                $ability_status_true = [1, 2, 3, 4];
                            } else if ($dota2_hero->id == 110) {
                                $ability_length = 8;
                                $ability_status_true = [1, 2, 3, 5];
                            } else if ($dota2_hero->id == 114) {
                                $ability_length = 8;
                                $ability_status_true = [1, 2, 5, 8];
                            }
                            for ($ability_idx = 1; $ability_idx <= $ability_length; $ability_idx++) {
                                if (array_key_exists('Ability'.$ability_idx, $hero)) {
                                    $dota2_ability = isset($dota2_abilities[$hero['Ability'.$ability_idx]]) ? $dota2_abilities[$hero['Ability'.$ability_idx]][0] : [];
                                    if ($dota2_ability) {
                                        $dota2_ability->name = ucwords(strtolower(str_replace('_', ' ', substr($hero['Ability'.$ability_idx], strlen(substr($hero_id, strlen('npc_dota_hero_')).'_')))));
                                        $dota2_ability->save();

                                        $status = 0;
                                        if (in_array($ability_idx, $ability_status_true)) {
                                            $status = 1;
                                        }

                                        $dota2_hero->abilities()->attach($dota2_ability->id, [
                                            'status' => $status,
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now()
                                        ]);
                                    }
                                }
                            }

                            // Talent
                            $ability_idx_start = 10;
                            $increment_plus_one = [10, 12, 14, 16];
                            if ($dota2_hero->id == 74) {
                                $ability_idx_start = 17;
                                $increment_plus_one = [17, 19, 21, 23];
                            } else if ($dota2_hero->id == 86) {
                                $ability_idx_start = 11;
                                $increment_plus_one = [11, 13, 15, 17];
                            } else if ($dota2_hero->id == 91) {
                                $ability_idx_start = 11;
                                $increment_plus_one = [11, 13, 15, 17];
                            }
                            for ($ability_idx = $ability_idx_start; $ability_idx < ($ability_idx_start + 8); $ability_idx++) {
                                $increment = -1;
                                if (in_array($ability_idx, $increment_plus_one)) {
                                    $increment = 1;
                                }
                                if (array_key_exists('Ability'.$ability_idx, $hero) && array_key_exists('Ability'.($ability_idx + $increment), $hero)) {
                                    $dota2_ability = isset($dota2_abilities[$hero['Ability'.$ability_idx]]) ? $dota2_abilities[$hero['Ability'.$ability_idx]][0] : [];
                                    $include_dota2_ability = isset($dota2_abilities[$hero['Ability'.($ability_idx + $increment)]]) ? $dota2_abilities[$hero['Ability'.($ability_idx + $increment)]][0] : [];
                                    if ($dota2_ability && $include_dota2_ability) {
                                        $dota2_hero->abilities()->attach($dota2_ability->id, [
                                            'include_dota2_abilities_id' => $include_dota2_ability->id,
                                            'status' => 1,
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now()
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }

        return response()->json(['code' => 200, 'message' => ['Update heroes success.']]);
    }

    public function updateItems()
    {
        set_time_limit(0);

        try {
            $items = VDFParse(storage_path('app/Dota 2/items.txt'));
            foreach ($items['DOTAAbilities'] as $item_id => $item) {
                if (is_array($item)) {
                    if (array_key_exists('ID', $item)) {
                        if ($item['ID'] != '0') {
                            $item_name = ucwords(strtolower(str_replace('_', ' ', substr($item_id, strlen('item_')))));
                            if (array_key_exists('ItemAliases', $item)) {
                                $item_alias = explode(';', $item['ItemAliases']);
                                $item_name = ucwords(strtolower($item_alias[count($item_alias) - 1]));
                            }

                            $dota2_item = Dota2Item::updateOrCreate([
                                'id' => $item['ID']
                            ], [
                                'name_id' => $item_id,
                                'name' => $item_name,
                                'picture_file_name' => file_exists(public_path().'/img/dota-2/items/'.substr($item_id, strlen('item_')).'.png') ? substr($item_id, strlen('item_')).'.png' : null
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => ['Something went wrong. Please try again.']]);
        }

        return response()->json(['code' => 200, 'message' => ['Update items success.']]);
    }

    public function test(Request $request)
    {
        set_time_limit(0);

        // $dota2_live_league_games = GuzzleHelper::requestDota2LiveLeagueGames();
        // echo "<pre>";
        // var_dump($dota2_live_league_games);
        // echo "</pre>";
        // die();

        // $arr1 = ['1', '2', '3', '4'];
        // $arr2 = [1, 2, 3, 4];
        // dd(array_diff($arr1, $arr2));

        // $tournaments = Tournament::select('id', 'leagues_id')
        //     ->with([
        //         'matches' => function($matches) {
        //             $matches->select('id', 'tournaments_id')
        //                 ->with([
        //                     'participants' => function($participants) {
        //                         $participants->select('tournaments_registrations.id AS id', 'matches_participants.side AS side')
        //                             ->with([
        //                                 'members' => function($members) {
        //                                     $members->select('members.id AS id', 'tournaments_registrations_details.steam32_id AS steam32_id');
        //                                 }
        //                             ]);
        //                     }
        //                 ])
        //                 ->whereHas('participants', function($participants) {
        //                     $participants->select('matches_participants.matches_id AS matches_id')
        //                         ->whereNull('matches_participants.matches_result')
        //                         ->where(function($side) {
        //                             $side->where('matches_participants.side', 1)
        //                                 ->orWhere('matches_participants.side', 2);
        //                         })
        //                         ->groupBy('matches_participants.matches_id')
        //                         ->havingRaw('COUNT(*) = 2');
        //                 });
        //         }
        //     ])
        //     ->whereNotNull('leagues_id')
        //     ->where('start', 1)
        //     ->where('complete', 0)
        //     ->get();

        // $leagues = [];
        // foreach ($tournaments as $tournament) {
        //     $matches = [];
        //     foreach ($tournament->matches as $match) {
        //         foreach ($match->participants as $participant) {
        //             $matches[$match->id][$participant->id] = (object) [
        //                 'steam32_id' => [],
        //                 'steam32_id_indicator' => []
        //             ];
        //             foreach ($participant->members as $member) {
        //                 array_push($matches[$match->id][$participant->id]->steam32_id, $member->steam32_id);
        //                 $matches[$match->id][$participant->id]->steam32_id_indicator[$member->steam32_id] = $member->id;
        //             }
        //         }
        //     }
        //     $leagues[$tournament->leagues_id] = $matches;
        // }

        // $dota2_live_match = Dota2LiveMatch::find('3235116774');
        // $radiant = $dota2_live_match->dota2_live_match_teams()->where('side', 1)->first();
        // $radiant_players = [];
        // $radiant_players_steam32_id = [];
        // foreach ($radiant->dota2_live_match_players as $player_idx => $player) {
        //     array_push($radiant_players_steam32_id, $player->steam32_id);
        //     $radiant_players[$player_idx] = $player;
        // }
        // $dire = $dota2_live_match->dota2_live_match_teams()->where('side', 2)->first();
        // $dire_players = [];
        // $dire_players_steam32_id = [];
        // foreach ($dire->dota2_live_match_players as $player_idx => $player) {
        //     array_push($dire_players_steam32_id, $player->steam32_id);
        //     $dire_players[$player_idx] = $player;
        // }

        // foreach ($leagues[5353] as $match_id => $participants) {
        //     $radiant_tournament_registration_id = null;
        //     $dire_tournament_registration_id = null;
        //     foreach ($participants as $tournament_registration_id => $participant) {
        //         if (!$radiant_tournament_registration_id) {
        //             if (!array_diff($participant->steam32_id, $radiant_players_steam32_id)) {
        //                 $radiant_tournament_registration_id = $tournament_registration_id;
        //                 continue;
        //             }
        //         }

        //         if (!$dire_tournament_registration_id) {
        //             if (!array_diff($participant->steam32_id, $dire_players_steam32_id)) {
        //                 $dire_tournament_registration_id = $tournament_registration_id;
        //                 continue;
        //             }
        //         }

        //         if ($radiant_tournament_registration_id && $dire_tournament_registration_id) {
        //             break;
        //         }
        //     }

        //     if ($radiant_tournament_registration_id && $dire_tournament_registration_id) {
        //         $dota2_live_match->match()->associate(Match::find($match_id));
        //         $dota2_live_match->save();

        //         $radiant->tournament_registration()->associate(TournamentRegistration::find($radiant_tournament_registration_id));
        //         $radiant->save();
        //         foreach ($radiant_players as $radiant_player) {
        //             $radiant_player->member()->associate(Member::find($participants[$radiant_tournament_registration_id]->steam32_id_indicator[$radiant_player->steam32_id]));
        //             $radiant_player->save();
        //         }

        //         $dire->tournament_registration()->associate(TournamentRegistration::find($dire_tournament_registration_id));
        //         $dire->save();
        //         foreach ($dire_players as $dire_player) {
        //             $dire_player->member()->associate(Member::find($participants[$dire_tournament_registration_id]->steam32_id_indicator[$dire_player->steam32_id]));
        //             $dire_player->save();
        //         }

        //         break;
        //     }
        // }

        // dd($leagues);

        /**
         * 
         * Delete Command (Dota 2 Live Match)
         * 
         */
        // $matches_id = ['3355405903'];
        // foreach ($matches_id as $match_id) {
        //     $dota2_live_match = Dota2LiveMatch::find($match_id);
        //     if ($dota2_live_match) {
        //         $dota2_live_match->durations()->delete();
        //         $radiant = $dota2_live_match->dota2_live_match_teams()->where('side', 1)->first();
        //         $dire = $dota2_live_match->dota2_live_match_teams()->where('side', 2)->first();

        //         if ($radiant) {
        //             $radiant->heroes_pick()->detach();
        //             $radiant->heroes_ban()->detach();

        //             $radiant_players = $radiant->dota2_live_match_players;
        //             foreach ($radiant_players as $radiant_player) {
        //                 $radiant_player->abilities()->detach();
        //                 $radiant_player->items()->detach();
        //                 $radiant_player->golds()->delete();
        //                 $radiant_player->xps()->delete();
        //                 $radiant_player->delete();
        //             }

        //             $radiant->delete();
        //         }

        //         if ($dire) {
        //             $dire->heroes_pick()->detach();
        //             $dire->heroes_ban()->detach();

        //             $dire_players = $dire->dota2_live_match_players;
        //             foreach ($dire_players as $dire_player) {
        //                 $dire_player->abilities()->detach();
        //                 $dire_player->items()->detach();
        //                 $dire_player->golds()->delete();
        //                 $dire_player->xps()->delete();
        //                 $dire_player->delete();
        //             }

        //             $dire->delete();
        //         }

        //         $dota2_live_match->delete();
        //     }
        // }
        // return "done";

        /**
         *
         * Socket Testing (Firing Broadcast)
         *
         */
        // $dota2_live_match = Dota2LiveMatch::find('3234831963');
        // event(new Dota2LiveMatchUpdated($dota2_live_match, [], [], [], []));
        // event(new Dota2LiveMatchPlayersItemsUpdated($dota2_live_match));
        // event(new Dota2LiveMatchRadiantPlayersUpdated($dota2_live_match, []));
        // event(new Dota2LiveMatchDirePlayersUpdated($dota2_live_match, []));
        // return "done";
    }
}
