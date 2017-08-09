<?php

namespace App\Console\Commands;

use App\Dota2Ability;
use App\Dota2Hero;
use App\Dota2LiveMatch;
use App\Dota2LiveMatchDurationLog;
use App\Dota2LiveMatchTeam;
use App\Dota2LiveMatchPlayer;
use App\Dota2LiveMatchPlayerGold;
use App\Dota2LiveMatchPlayerXP;
use App\Events\Dota2LiveMatchDirePlayersUpdated;
use App\Events\Dota2LiveMatchPlayersItemsUpdated;
use App\Events\Dota2LiveMatchRadiantPlayersUpdated;
use App\Events\Dota2LiveMatchUpdated;
use App\Helpers\GuzzleHelper;
use App\Match;
use App\Member;
use App\Tournament;
use App\TournamentRegistration;
use Carbon;
use DB;
use Illuminate\Console\Command;

class RetrieveAndUpdateDota2LiveMatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dota2_live_match:retrieve_and_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve Dota 2 API (GetLiveLeagueGames) & Update to Database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);

        $dota2_live_matches = Dota2LiveMatch::select('*')
            ->whereHas('dota2_live_match_teams', function($dota2_live_match_teams) {
                $dota2_live_match_teams->select('dota2_live_matches_id')
                    ->whereNull('matches_result')
                    ->where(function($side) {
                        $side->where('side', 1)
                            ->orWhere('side', 2);
                    })
                    ->groupBy('dota2_live_matches_id')
                    ->havingRaw('COUNT(*) = 2');
            })
            ->get()
            ->groupBy('id');

        $dota2_live_league_games = GuzzleHelper::requestDota2LiveLeagueGames();
        if ($dota2_live_league_games) {
            $tournaments = Tournament::select('id', 'leagues_id')
                ->with([
                    'matches' => function($matches) {
                        $matches->select('id', 'tournaments_id')
                            ->with([
                                'participants' => function($participants) {
                                    $participants->select('tournaments_registrations.id AS id', 'matches_participants.side AS side')
                                        ->with([
                                            'members' => function($members) {
                                                $members->select('members.id AS id', 'tournaments_registrations_details.steam32_id AS steam32_id');
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
                            });
                    }
                ])
                ->whereNotNull('leagues_id')
                ->where('start', 1)
                ->where('complete', 0)
                ->get();

            $leagues = [];
            foreach ($tournaments as $tournament) {
                $matches = [];
                foreach ($tournament->matches as $match) {
                    foreach ($match->participants as $participant) {
                        $matches[$match->id][$participant->id] = (object) [
                            'steam32_id' => [],
                            'steam32_id_indicator' => []
                        ];
                        foreach ($participant->members as $member) {
                            array_push($matches[$match->id][$participant->id]->steam32_id, $member->steam32_id);
                            $matches[$match->id][$participant->id]->steam32_id_indicator[$member->steam32_id] = $member->id;
                        }
                    }
                }
                $leagues[$tournament->leagues_id] = $matches;
            }

            // $leagues['5353'] = [];
            if ($leagues) {
                // $dota2_abilities = Dota2Ability::select('id', 'name', 'picture_file_name')
                //     ->with([
                //         'heroes' => function($heroes) {
                //             $heroes->select('dota2_heroes.id')
                //                 ->where('dota2_heroes_abilities.status', 1);
                //         }
                //     ])
                //     ->where('name_id', 'NOT LIKE', 'special_bonus_%')
                //     ->whereHas('heroes', function($heroes) {
                //         $heroes->where('dota2_heroes_abilities.status', 1);
                //     })
                //     ->get()
                //     ->groupBy('id');
                $dota2_abilities = Dota2Ability::select('id', 'name', 'picture_file_name')
                    ->with([
                        'heroes' => function($heroes) {
                            $heroes->select('dota2_heroes.id')
                                ->where('dota2_heroes_abilities.status', 1);
                        }
                    ])
                    ->whereHas('heroes', function($heroes) {
                        $heroes->where('dota2_heroes_abilities.status', 1);
                    })
                    ->get()
                    ->groupBy('id');
                $dota2_heroes = Dota2Hero::select('id')
                    ->with([
                        'abilities' => function($abilities) {
                            $abilities->select('dota2_abilities.id')
                                ->withPivot('include_dota2_abilities_id')
                                ->where('dota2_heroes_abilities.status', 1);
                        }
                    ])
                    ->get()
                    ->groupBy('id');

                if (isset($dota2_live_league_games->games)) {
                    if (isset($dota2_live_league_games->games->game)) {
                        if (is_array($dota2_live_league_games->games->game)) {
                            foreach ($dota2_live_league_games->games->game as $game) {
                                if (array_key_exists($game->league_id, $leagues)) {
                                    $players = [];
                                    foreach ($game->players->player as $player) {
                                        $players[$player->account_id] = $player->name;
                                    }
                                    $duration = (int) ceil($game->scoreboard->duration);

                                    $dota2_live_match = Dota2LiveMatch::find($game->match_id);
                                    if ($dota2_live_match) {
                                        if (isset($dota2_live_matches->{$game->match_id})) {
                                            unset($dota2_live_matches->{$game->match_id});
                                        }

                                        $old_duration = $dota2_live_match->duration;
                                        $timelapse = $duration - $old_duration;

                                        DB::beginTransaction();
                                        try {
                                            $dota2_live_match->spectators = $game->spectators;
                                            $dota2_live_match->duration = $duration;
                                            $dota2_live_match->roshan_respawn_timer = $game->scoreboard->roshan_respawn_timer;
                                            $dota2_live_match->save();

                                            if ($timelapse > 0) {
                                                $dota2_live_match_duration_log = new Dota2LiveMatchDurationLog([
                                                    'duration' => $duration
                                                ]);
                                                $dota2_live_match->durations()->save($dota2_live_match_duration_log);
                                            }

                                            $radiant = $dota2_live_match->dota2_live_match_teams()->where('side', 1)->first();
                                            if ($radiant) {
                                                $radiant->score = $game->scoreboard->radiant->score;
                                                $radiant->tower_state = $game->scoreboard->radiant->tower_state;
                                                $radiant->barracks_state = $game->scoreboard->radiant->barracks_state;
                                                $radiant->save();

                                                $pick_amount = $radiant->heroes_pick()->count();
                                                if (isset($game->scoreboard->radiant->picks)) {
                                                    if (is_array($game->scoreboard->radiant->picks->pick)) {
                                                        for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->radiant->picks->pick); $pick_idx++) {
                                                            $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick[$pick_idx]->hero_id, [
                                                                'pick_order' => $pick_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    } else {
                                                        if ($pick_amount < 1) {
                                                            $pick_idx = $pick_amount;
                                                            $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick->hero_id, [
                                                                'pick_order' => $pick_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }
                                                }

                                                $ban_amount = $radiant->heroes_ban()->count();
                                                if (isset($game->scoreboard->radiant->bans)) {
                                                    if (is_array($game->scoreboard->radiant->bans->ban)) {
                                                        for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->radiant->bans->ban); $ban_idx++) {
                                                            $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban[$ban_idx]->hero_id, [
                                                                'ban_order' => $ban_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    } else {
                                                        if ($ban_amount < 1) {
                                                            $ban_idx = $ban_amount;
                                                            $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban->hero_id, [
                                                                'ban_order' => $ban_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }
                                                }

                                                $radiant_players_exists_in_db = $radiant->dota2_live_match_players->groupBy('steam32_id');
                                                $radiant_players = [];
                                                $radiant_players_hero_indicator = [];
                                                $radiant_golds = [];
                                                $radiant_xps = [];
                                                if (is_array($game->scoreboard->radiant->players->player)) {
                                                    foreach ($game->scoreboard->radiant->players->player as $player_idx => $player) {
                                                        $radiant_player = null;
                                                        if (isset($radiant_players_exists_in_db[$player->account_id])) {
                                                            $radiant_player = $radiant_players_exists_in_db[$player->account_id][0];
                                                        }

                                                        if ($radiant_player) {
                                                            $radiant_player->kills = $player->kills;
                                                            $radiant_player->death = $player->death;
                                                            $radiant_player->assists = $player->assists;
                                                            $radiant_player->last_hits = $player->last_hits;
                                                            $radiant_player->denies = $player->denies;
                                                            $radiant_player->gold = $player->gold;
                                                            $radiant_player->level = $player->level;
                                                            $radiant_player->gold_per_min = $player->gold_per_min;
                                                            $radiant_player->xp_per_min = $player->xp_per_min;
                                                            $radiant_player->respawn_timer = $player->respawn_timer;
                                                            $radiant_player->position_x = $player->position_x;
                                                            $radiant_player->position_y = $player->position_y;
                                                            $radiant_player->net_worth = $player->net_worth;
                                                            if ($player->hero_id != 0) {
                                                                $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                            }
                                                            $radiant_player->save();

                                                            $radiant_player->items()->detach();
                                                            for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                                if ($player->{'item'.$item_idx} != 0) {
                                                                    $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                                        'item_order' => $item_idx + 1,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                }
                                                            }

                                                            if ($timelapse > 0) {
                                                                $last_gold = $radiant_player->golds()->where('duration', $old_duration)->first();
                                                                if ($last_gold) {
                                                                    $last_gold = $last_gold->gold;
                                                                } else {
                                                                    $last_gold = 625;
                                                                }
                                                                $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                                    'gold_per_min' => $player->gold_per_min,
                                                                    'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                                    'net_worth' => $player->net_worth,
                                                                    'duration' => $duration
                                                                ]);
                                                                $radiant_player->golds()->save($radiant_player_gold);
                                                                array_push($radiant_golds, (object) [
                                                                    'id' => $radiant_player->id,
                                                                    'net_worth' => $player->net_worth
                                                                ]);

                                                                $last_xp = $radiant_player->xps()->where('duration', $old_duration)->first();
                                                                if ($last_xp) {
                                                                    $last_xp = $last_xp->xp;
                                                                } else {
                                                                    $last_xp = 0;
                                                                }
                                                                $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                                    'xp_per_min' => $player->xp_per_min,
                                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                                    'duration' => $duration
                                                                ]);
                                                                $radiant_player->xps()->save($radiant_player_xp);
                                                                array_push($radiant_xps, (object) [
                                                                    'id' => $radiant_player->id,
                                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                                ]);
                                                            }

                                                            $radiant_players[$player_idx] = $radiant_player;
                                                            if ($player->hero_id != 0) {
                                                                $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $player_idx = 0;
                                                    $player = $game->scoreboard->radiant->players->player;

                                                    $radiant_player = null;
                                                    if (isset($radiant_players_exists_in_db[$player->account_id])) {
                                                        $radiant_player = $radiant_players_exists_in_db[$player->account_id][0];
                                                    }

                                                    if ($radiant_player) {
                                                        $radiant_player->kills = $player->kills;
                                                        $radiant_player->death = $player->death;
                                                        $radiant_player->assists = $player->assists;
                                                        $radiant_player->last_hits = $player->last_hits;
                                                        $radiant_player->denies = $player->denies;
                                                        $radiant_player->gold = $player->gold;
                                                        $radiant_player->level = $player->level;
                                                        $radiant_player->gold_per_min = $player->gold_per_min;
                                                        $radiant_player->xp_per_min = $player->xp_per_min;
                                                        $radiant_player->respawn_timer = $player->respawn_timer;
                                                        $radiant_player->position_x = $player->position_x;
                                                        $radiant_player->position_y = $player->position_y;
                                                        $radiant_player->net_worth = $player->net_worth;
                                                        if ($player->hero_id != 0) {
                                                            $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                        }
                                                        $radiant_player->save();

                                                        $radiant_player->items()->detach();
                                                        for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                            if ($player->{'item'.$item_idx} != 0) {
                                                                $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                                    'item_order' => $item_idx + 1,
                                                                    'created_at' => Carbon::now(),
                                                                    'updated_at' => Carbon::now()
                                                                ]);
                                                            }
                                                        }

                                                        if ($timelapse > 0) {
                                                            $last_gold = $radiant_player->golds()->where('duration', $old_duration)->first();
                                                            if ($last_gold) {
                                                                $last_gold = $last_gold->gold;
                                                            } else {
                                                                $last_gold = 625;
                                                            }
                                                            $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                                'gold_per_min' => $player->gold_per_min,
                                                                'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                                'net_worth' => $player->net_worth,
                                                                'duration' => $duration
                                                            ]);
                                                            $radiant_player->golds()->save($radiant_player_gold);
                                                            array_push($radiant_golds, (object) [
                                                                'id' => $radiant_player->id,
                                                                'net_worth' => $player->net_worth
                                                            ]);

                                                            $last_xp = $radiant_player->xps()->where('duration', $old_duration)->first();
                                                            if ($last_xp) {
                                                                $last_xp = $last_xp->xp;
                                                            } else {
                                                                $last_xp = 0;
                                                            }
                                                            $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                                'xp_per_min' => $player->xp_per_min,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                                'duration' => $duration
                                                            ]);
                                                            $radiant_player->xps()->save($radiant_player_xp);
                                                            array_push($radiant_xps, (object) [
                                                                'id' => $radiant_player->id,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                            ]);
                                                        }

                                                        $radiant_players[$player_idx] = $radiant_player;
                                                        if ($player->hero_id != 0) {
                                                            $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                        }
                                                    }
                                                }

                                                $radiant_abilities = [];
                                                if (isset($game->scoreboard->radiant->abilities)) {
                                                    if (is_array($game->scoreboard->radiant->abilities)) {
                                                        if (count($game->scoreboard->radiant->abilities) < count($radiant_players)) {
                                                            foreach ($game->scoreboard->radiant->abilities as $abilities) {
                                                                $current_player = null;
                                                                $current_player_hero = null;
                                                                $current_player_abilities = [];
                                                                $ability_order = count($current_player_abilities) + 1;

                                                                if (is_array($abilities->ability)) {
                                                                    foreach ($abilities->ability as $ability) {
                                                                        if (!$current_player) {
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                                if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                                    if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                        $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                        $current_player_abilities = $current_player->abilities;
                                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                    }
                                                                                }
                                                                            }
                                                                        }

                                                                        if ($current_player) {
                                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                                $abilities_detail = null;
                                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                    $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                                }

                                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                                        'ability_order' => $ability_order,
                                                                                        'created_at' => Carbon::now(),
                                                                                        'updated_at' => Carbon::now()
                                                                                    ]);

                                                                                    if ($abilities_detail) {
                                                                                        array_push($radiant_abilities, (object) [
                                                                                            'id' => $current_player->id,
                                                                                            'name' => $abilities_detail->name,
                                                                                            'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                            'ability_order' => $ability_order
                                                                                        ]);
                                                                                    }

                                                                                    $ability_order++;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $ability = $abilities->ability;

                                                                    if (!$current_player) {
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                            if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                                if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                    $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                    $current_player_abilities = $current_player->abilities;
                                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if ($current_player) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                            $abilities_detail = null;
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                            }

                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);

                                                                                if ($abilities_detail) {
                                                                                    array_push($radiant_abilities, (object) [
                                                                                        'id' => $current_player->id,
                                                                                        'name' => $abilities_detail->name,
                                                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                        'ability_order' => $ability_order
                                                                                    ]);
                                                                                }

                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else if (count($game->scoreboard->radiant->abilities) == count($radiant_players)) {
                                                            foreach ($game->scoreboard->radiant->abilities as $abilities_idx => $abilities) {
                                                                $current_player = $radiant_players[$abilities_idx];
                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                $current_player_abilities = $current_player->abilities;
                                                                $ability_order = count($current_player_abilities) + 1;
                                                                $current_player_abilities = $current_player_abilities->groupBy('id');

                                                                if (is_array($abilities->ability)) {
                                                                    foreach ($abilities->ability as $ability) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                            $abilities_detail = null;
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                            }

                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);

                                                                                if ($abilities_detail) {
                                                                                    array_push($radiant_abilities, (object) [
                                                                                        'id' => $current_player->id,
                                                                                        'name' => $abilities_detail->name,
                                                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                        'ability_order' => $ability_order
                                                                                    ]);
                                                                                }

                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $ability = $abilities->ability;

                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($radiant_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            // Dota 2 API Data must be wrong.
                                                        }
                                                    } else {
                                                        $abilities = $game->scoreboard->radiant->abilities;

                                                        $current_player = null;
                                                        $current_player_hero = null;
                                                        $current_player_abilities = [];
                                                        $ability_order = count($current_player_abilities) + 1;

                                                        if (is_array($abilities->ability)) {
                                                            foreach ($abilities->ability as $ability) {
                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                            if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($radiant_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $ability = $abilities->ability;

                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                        if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                            $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                    $abilities_detail = null;
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                    }

                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);

                                                                        if ($abilities_detail) {
                                                                            array_push($radiant_abilities, (object) [
                                                                                'id' => $current_player->id,
                                                                                'name' => $abilities_detail->name,
                                                                                'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                'ability_order' => $ability_order
                                                                            ]);
                                                                        }

                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            $dire = $dota2_live_match->dota2_live_match_teams()->where('side', 2)->first();
                                            if ($dire) {
                                                $dire->score = $game->scoreboard->dire->score;
                                                $dire->tower_state = $game->scoreboard->dire->tower_state;
                                                $dire->barracks_state = $game->scoreboard->dire->barracks_state;
                                                $dire->save();

                                                $pick_amount = $dire->heroes_pick()->count();
                                                if (isset($game->scoreboard->dire->picks)) {
                                                    if (is_array($game->scoreboard->dire->picks->pick)) {
                                                        for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->dire->picks->pick); $pick_idx++) {
                                                            $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick[$pick_idx]->hero_id, [
                                                                'pick_order' => $pick_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    } else {
                                                        if ($pick_amount < 1) {
                                                            $pick_idx = $pick_amount;
                                                            $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick->hero_id, [
                                                                'pick_order' => $pick_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }
                                                }

                                                $ban_amount = $dire->heroes_ban()->count();
                                                if (isset($game->scoreboard->dire->bans)) {
                                                    if (is_array($game->scoreboard->dire->bans->ban)) {
                                                        for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->dire->bans->ban); $ban_idx++) {
                                                            $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban[$ban_idx]->hero_id, [
                                                                'ban_order' => $ban_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    } else {
                                                        if ($ban_amount < 1) {
                                                            $ban_idx = $ban_amount;
                                                            $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban->hero_id, [
                                                                'ban_order' => $ban_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }
                                                }

                                                $dire_players_exists_in_db = $dire->dota2_live_match_players->groupBy('steam32_id');
                                                $dire_players = [];
                                                $dire_players_hero_indicator = [];
                                                $dire_golds = [];
                                                $dire_xps = [];
                                                if (is_array($game->scoreboard->dire->players->player)) {
                                                    foreach ($game->scoreboard->dire->players->player as $player_idx => $player) {
                                                        $dire_player = null;
                                                        if (isset($dire_players_exists_in_db[$player->account_id])) {
                                                            $dire_player = $dire_players_exists_in_db[$player->account_id][0];
                                                        }

                                                        if ($dire_player) {
                                                            $dire_player->kills = $player->kills;
                                                            $dire_player->death = $player->death;
                                                            $dire_player->assists = $player->assists;
                                                            $dire_player->last_hits = $player->last_hits;
                                                            $dire_player->denies = $player->denies;
                                                            $dire_player->gold = $player->gold;
                                                            $dire_player->level = $player->level;
                                                            $dire_player->gold_per_min = $player->gold_per_min;
                                                            $dire_player->xp_per_min = $player->xp_per_min;
                                                            $dire_player->respawn_timer = $player->respawn_timer;
                                                            $dire_player->position_x = $player->position_x;
                                                            $dire_player->position_y = $player->position_y;
                                                            $dire_player->net_worth = $player->net_worth;
                                                            if ($player->hero_id != 0) {
                                                                $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                            }
                                                            $dire_player->save();

                                                            $dire_player->items()->detach();
                                                            for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                                if ($player->{'item'.$item_idx} != 0) {
                                                                    $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                                        'item_order' => $item_idx + 1,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                }
                                                            }

                                                            if ($timelapse > 0) {
                                                                $last_gold = $dire_player->golds()->where('duration', $old_duration)->first();
                                                                if ($last_gold) {
                                                                    $last_gold = $last_gold->gold;
                                                                } else {
                                                                    $last_gold = 625;
                                                                }
                                                                $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                                    'gold_per_min' => $player->gold_per_min,
                                                                    'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                                    'net_worth' => $player->net_worth,
                                                                    'duration' => $duration
                                                                ]);
                                                                $dire_player->golds()->save($dire_player_gold);
                                                                array_push($dire_golds, (object) [
                                                                    'id' => $dire_player->id,
                                                                    'net_worth' => $player->net_worth
                                                                ]);

                                                                $last_xp = $dire_player->xps()->where('duration', $old_duration)->first();
                                                                if ($last_xp) {
                                                                    $last_xp = $last_xp->xp;
                                                                } else {
                                                                    $last_xp = 0;
                                                                }
                                                                $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                                    'xp_per_min' => $player->xp_per_min,
                                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                                    'duration' => $duration
                                                                ]);
                                                                $dire_player->xps()->save($dire_player_xp);
                                                                array_push($dire_xps, (object) [
                                                                    'id' => $dire_player->id,
                                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                                ]);
                                                            }

                                                            $dire_players[$player_idx] = $dire_player;
                                                            if ($player->hero_id != 0) {
                                                                $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $player_idx = 0;
                                                    $player = $game->scoreboard->dire->players->player;

                                                    $dire_player = null;
                                                    if (isset($dire_players_exists_in_db[$player->account_id])) {
                                                        $dire_player = $dire_players_exists_in_db[$player->account_id][0];
                                                    }

                                                    if ($dire_player) {
                                                        $dire_player->kills = $player->kills;
                                                        $dire_player->death = $player->death;
                                                        $dire_player->assists = $player->assists;
                                                        $dire_player->last_hits = $player->last_hits;
                                                        $dire_player->denies = $player->denies;
                                                        $dire_player->gold = $player->gold;
                                                        $dire_player->level = $player->level;
                                                        $dire_player->gold_per_min = $player->gold_per_min;
                                                        $dire_player->xp_per_min = $player->xp_per_min;
                                                        $dire_player->respawn_timer = $player->respawn_timer;
                                                        $dire_player->position_x = $player->position_x;
                                                        $dire_player->position_y = $player->position_y;
                                                        $dire_player->net_worth = $player->net_worth;
                                                        if ($player->hero_id != 0) {
                                                            $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                        }
                                                        $dire_player->save();

                                                        $dire_player->items()->detach();
                                                        for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                            if ($player->{'item'.$item_idx} != 0) {
                                                                $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                                    'item_order' => $item_idx + 1,
                                                                    'created_at' => Carbon::now(),
                                                                    'updated_at' => Carbon::now()
                                                                ]);
                                                            }
                                                        }

                                                        if ($timelapse > 0) {
                                                            $last_gold = $dire_player->golds()->where('duration', $old_duration)->first();
                                                            if ($last_gold) {
                                                                $last_gold = $last_gold->gold;
                                                            } else {
                                                                $last_gold = 625;
                                                            }
                                                            $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                                'gold_per_min' => $player->gold_per_min,
                                                                'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                                'net_worth' => $player->net_worth,
                                                                'duration' => $duration
                                                            ]);
                                                            $dire_player->golds()->save($dire_player_gold);
                                                            array_push($dire_golds, (object) [
                                                                'id' => $dire_player->id,
                                                                'net_worth' => $player->net_worth
                                                            ]);

                                                            $last_xp = $dire_player->xps()->where('duration', $old_duration)->first();
                                                            if ($last_xp) {
                                                                $last_xp = $last_xp->xp;
                                                            } else {
                                                                $last_xp = 0;
                                                            }
                                                            $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                                'xp_per_min' => $player->xp_per_min,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                                'duration' => $duration
                                                            ]);
                                                            $dire_player->xps()->save($dire_player_xp);
                                                            array_push($dire_xps, (object) [
                                                                'id' => $dire_player->id,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                            ]);
                                                        }

                                                        $dire_players[$player_idx] = $dire_player;
                                                        if ($player->hero_id != 0) {
                                                            $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                        }
                                                    }
                                                }

                                                $dire_abilities = [];
                                                if (isset($game->scoreboard->dire->abilities)) {
                                                    if (is_array($game->scoreboard->dire->abilities)) {
                                                        if (count($game->scoreboard->dire->abilities) < count($dire_players)) {
                                                            foreach ($game->scoreboard->dire->abilities as $abilities) {
                                                                $current_player = null;
                                                                $current_player_hero = null;
                                                                $current_player_abilities = [];
                                                                $ability_order = count($current_player_abilities) + 1;

                                                                if (is_array($abilities->ability)) {
                                                                    foreach ($abilities->ability as $ability) {
                                                                        if (!$current_player) {
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                                if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                                    if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                        $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                        $current_player_abilities = $current_player->abilities;
                                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                    }
                                                                                }
                                                                            }
                                                                        }

                                                                        if ($current_player) {
                                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                                $abilities_detail = null;
                                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                    $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                                }

                                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                                        'ability_order' => $ability_order,
                                                                                        'created_at' => Carbon::now(),
                                                                                        'updated_at' => Carbon::now()
                                                                                    ]);

                                                                                    if ($abilities_detail) {
                                                                                        array_push($dire_abilities, (object) [
                                                                                            'id' => $current_player->id,
                                                                                            'name' => $abilities_detail->name,
                                                                                            'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                            'ability_order' => $ability_order
                                                                                        ]);
                                                                                    }

                                                                                    $ability_order++;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $ability = $abilities->ability;

                                                                    if (!$current_player) {
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                            if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                                if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                    $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                    $current_player_abilities = $current_player->abilities;
                                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if ($current_player) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                            $abilities_detail = null;
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                            }

                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);

                                                                                if ($abilities_detail) {
                                                                                    array_push($dire_abilities, (object) [
                                                                                        'id' => $current_player->id,
                                                                                        'name' => $abilities_detail->name,
                                                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                        'ability_order' => $ability_order
                                                                                    ]);
                                                                                }

                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else if (count($game->scoreboard->dire->abilities) == count($dire_players)) {
                                                            foreach ($game->scoreboard->dire->abilities as $abilities_idx => $abilities) {
                                                                $current_player = $dire_players[$abilities_idx];
                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                $current_player_abilities = $current_player->abilities;
                                                                $ability_order = count($current_player_abilities) + 1;
                                                                $current_player_abilities = $current_player_abilities->groupBy('id');

                                                                if (is_array($abilities->ability)) {
                                                                    foreach ($abilities->ability as $ability) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                            $abilities_detail = null;
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                            }

                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);

                                                                                if ($abilities_detail) {
                                                                                    array_push($dire_abilities, (object) [
                                                                                        'id' => $current_player->id,
                                                                                        'name' => $abilities_detail->name,
                                                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                        'ability_order' => $ability_order
                                                                                    ]);
                                                                                }

                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $ability = $abilities->ability;

                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($dire_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            // Dota 2 API Data must be wrong.
                                                        }
                                                    } else {
                                                        $abilities = $game->scoreboard->dire->abilities;

                                                        $current_player = null;
                                                        $current_player_hero = null;
                                                        $current_player_abilities = [];
                                                        $ability_order = count($current_player_abilities) + 1;

                                                        if (is_array($abilities->ability)) {
                                                            foreach ($abilities->ability as $ability) {
                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                            if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($dire_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $ability = $abilities->ability;

                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                        if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                            $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                    $abilities_detail = null;
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                    }

                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);

                                                                        if ($abilities_detail) {
                                                                            array_push($dire_abilities, (object) [
                                                                                'id' => $current_player->id,
                                                                                'name' => $abilities_detail->name,
                                                                                'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                'ability_order' => $ability_order
                                                                            ]);
                                                                        }

                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            DB::commit();

                                            event(new Dota2LiveMatchUpdated($dota2_live_match, $radiant_golds, $radiant_xps, $dire_golds, $dire_xps));
                                            event(new Dota2LiveMatchPlayersItemsUpdated($dota2_live_match));
                                            event(new Dota2LiveMatchRadiantPlayersUpdated($dota2_live_match, $radiant_abilities));
                                            event(new Dota2LiveMatchDirePlayersUpdated($dota2_live_match, $dire_abilities));
                                        } catch (\Exception $e) {
                                            DB::rollBack();
                                            // dd($e->getMessage());
                                        }
                                    } else {
                                        $timelapse = $duration;

                                        DB::beginTransaction();
                                        try {
                                            $dota2_live_match = new Dota2LiveMatch([
                                                'id' => $game->match_id,
                                                'leagues_id' => $game->league_id,
                                                'series_type' => $game->series_type,
                                                'spectators' => $game->spectators,
                                                'duration' => $duration,
                                                'roshan_respawn_timer' => $game->scoreboard->roshan_respawn_timer
                                            ]);
                                            $dota2_live_match->save();

                                            $dota2_live_match_duration_log = new Dota2LiveMatchDurationLog([
                                                'duration' => $duration
                                            ]);
                                            $dota2_live_match->durations()->save($dota2_live_match_duration_log);

                                            $radiant = new Dota2LiveMatchTeam([
                                                'dota2_teams_id' => isset($game->radiant_team) ? $game->radiant_team->team_id : null,
                                                'dota2_teams_name' => isset($game->radiant_team) ? $game->radiant_team->team_name : null,
                                                'dota2_teams_logo' => isset($game->radiant_team) ? $game->radiant_team->team_logo : null,
                                                'series_wins' => $game->radiant_series_wins,
                                                'score' => $game->scoreboard->radiant->score,
                                                'tower_state' => $game->scoreboard->radiant->tower_state,
                                                'barracks_state' => $game->scoreboard->radiant->barracks_state,
                                                'side' => 1
                                            ]);
                                            $dota2_live_match->dota2_live_match_teams()->save($radiant);

                                            $pick_amount = 0;
                                            if (isset($game->scoreboard->radiant->picks)) {
                                                if (is_array($game->scoreboard->radiant->picks->pick)) {
                                                    for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->radiant->picks->pick); $pick_idx++) {
                                                        $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick[$pick_idx]->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($pick_amount < 1) {
                                                        $pick_idx = $pick_amount;
                                                        $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $ban_amount = 0;
                                            if (isset($game->scoreboard->radiant->bans)) {
                                                if (is_array($game->scoreboard->radiant->bans->ban)) {
                                                    for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->radiant->bans->ban); $ban_idx++) {
                                                        $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban[$ban_idx]->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($ban_amount < 1) {
                                                        $ban_idx = $ban_amount;
                                                        $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $radiant_players = [];
                                            $radiant_players_hero_indicator = [];
                                            $radiant_players_steam32_id = [];
                                            if (is_array($game->scoreboard->radiant->players->player)) {
                                                foreach ($game->scoreboard->radiant->players->player as $player_idx => $player) {
                                                    $radiant_player = new Dota2LiveMatchPlayer([
                                                        'steam32_id' => $player->account_id,
                                                        'name' => $players[$player->account_id],
                                                        'kills' => $player->kills,
                                                        'death' => $player->death,
                                                        'assists' => $player->assists,
                                                        'last_hits' => $player->last_hits,
                                                        'denies' => $player->denies,
                                                        'gold' => $player->gold,
                                                        'level' => $player->level,
                                                        'gold_per_min' => $player->gold_per_min,
                                                        'xp_per_min' => $player->xp_per_min,
                                                        'respawn_timer' => $player->respawn_timer,
                                                        'position_x' => $player->position_x,
                                                        'position_y' => $player->position_y,
                                                        'net_worth' => $player->net_worth,
                                                        'player_order' => $player->player_slot
                                                    ]);
                                                    if ($player->hero_id != 0) {
                                                        $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                    }
                                                    $radiant->dota2_live_match_players()->save($radiant_player);

                                                    for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                        if ($player->{'item'.$item_idx} != 0) {
                                                            $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                                'item_order' => $item_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }

                                                    $last_gold = 625;
                                                    $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                        'gold_per_min' => $player->gold_per_min,
                                                        'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                        'net_worth' => $player->net_worth,
                                                        'duration' => $duration
                                                    ]);
                                                    $radiant_player->golds()->save($radiant_player_gold);

                                                    $last_xp = 0;
                                                    $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                        'xp_per_min' => $player->xp_per_min,
                                                        'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                        'duration' => $duration
                                                    ]);
                                                    $radiant_player->xps()->save($radiant_player_xp);

                                                    $radiant_players[$player_idx] = $radiant_player;
                                                    array_push($radiant_players_steam32_id, $player->account_id);
                                                    if ($player->hero_id != 0) {
                                                        $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                    }
                                                }
                                            } else {
                                                $player_idx = 0;
                                                $player = $game->scoreboard->radiant->players->player;

                                                $radiant_player = new Dota2LiveMatchPlayer([
                                                    'steam32_id' => $player->account_id,
                                                    'name' => $players[$player->account_id],
                                                    'kills' => $player->kills,
                                                    'death' => $player->death,
                                                    'assists' => $player->assists,
                                                    'last_hits' => $player->last_hits,
                                                    'denies' => $player->denies,
                                                    'gold' => $player->gold,
                                                    'level' => $player->level,
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'respawn_timer' => $player->respawn_timer,
                                                    'position_x' => $player->position_x,
                                                    'position_y' => $player->position_y,
                                                    'net_worth' => $player->net_worth,
                                                    'player_order' => $player->player_slot
                                                ]);
                                                if ($player->hero_id != 0) {
                                                    $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                }
                                                $radiant->dota2_live_match_players()->save($radiant_player);

                                                for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                    if ($player->{'item'.$item_idx} != 0) {
                                                        $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                            'item_order' => $item_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }

                                                $last_gold = 625;
                                                $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                    'net_worth' => $player->net_worth,
                                                    'duration' => $duration
                                                ]);
                                                $radiant_player->golds()->save($radiant_player_gold);

                                                $last_xp = 0;
                                                $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                    'duration' => $duration
                                                ]);
                                                $radiant_player->xps()->save($radiant_player_xp);

                                                $radiant_players[$player_idx] = $radiant_player;
                                                array_push($radiant_players_steam32_id, $player->account_id);
                                                if ($player->hero_id != 0) {
                                                    $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                }
                                            }

                                            if (isset($game->scoreboard->radiant->abilities)) {
                                                if (is_array($game->scoreboard->radiant->abilities)) {
                                                    if (count($game->scoreboard->radiant->abilities) < count($radiant_players)) {
                                                        foreach ($game->scoreboard->radiant->abilities as $abilities) {
                                                            $current_player = null;
                                                            $current_player_hero = null;
                                                            $current_player_abilities = [];
                                                            $ability_order = count($current_player_abilities) + 1;

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if (!$current_player) {
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                            if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                                if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                    $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                    $current_player_abilities = $current_player->abilities;
                                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if ($current_player) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);
                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                            if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);
                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else if (count($game->scoreboard->radiant->abilities) == count($radiant_players)) {
                                                        foreach ($game->scoreboard->radiant->abilities as $abilities_idx => $abilities) {
                                                            $current_player = $radiant_players[$abilities_idx];
                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                            $current_player_abilities = $current_player->abilities;
                                                            $ability_order = count($current_player_abilities) + 1;
                                                            $current_player_abilities = $current_player_abilities->groupBy('id');

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);
                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        // Dota 2 API Data must be wrong.
                                                    }
                                                } else {
                                                    $abilities = $game->scoreboard->radiant->abilities;

                                                    $current_player = null;
                                                    $current_player_hero = null;
                                                    $current_player_abilities = [];
                                                    $ability_order = count($current_player_abilities) + 1;

                                                    if (is_array($abilities->ability)) {
                                                        foreach ($abilities->ability as $ability) {
                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                        if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                            $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $ability = $abilities->ability;

                                                        if (!$current_player) {
                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                    if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                        $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                        $current_player_abilities = $current_player->abilities;
                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($current_player) {
                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            $dire = new Dota2LiveMatchTeam([
                                                'dota2_teams_id' => isset($game->dire_team) ? $game->dire_team->team_id : null,
                                                'dota2_teams_name' => isset($game->dire_team) ? $game->dire_team->team_name : null,
                                                'dota2_teams_logo' => isset($game->dire_team) ? $game->dire_team->team_logo : null,
                                                'series_wins' => $game->dire_series_wins,
                                                'score' => $game->scoreboard->dire->score,
                                                'tower_state' => $game->scoreboard->dire->tower_state,
                                                'barracks_state' => $game->scoreboard->dire->barracks_state,
                                                'side' => 2
                                            ]);
                                            $dota2_live_match->dota2_live_match_teams()->save($dire);

                                            $pick_amount = 0;
                                            if (isset($game->scoreboard->dire->picks)) {
                                                if (is_array($game->scoreboard->dire->picks->pick)) {
                                                    for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->dire->picks->pick); $pick_idx++) {
                                                        $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick[$pick_idx]->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($pick_amount < 1) {
                                                        $pick_idx = $pick_amount;
                                                        $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $ban_amount = 0;
                                            if (isset($game->scoreboard->dire->bans)) {
                                                if (is_array($game->scoreboard->dire->bans->ban)) {
                                                    for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->dire->bans->ban); $ban_idx++) {
                                                        $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban[$ban_idx]->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($ban_amount < 1) {
                                                        $ban_idx = $ban_amount;
                                                        $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $dire_players = [];
                                            $dire_players_hero_indicator = [];
                                            $dire_players_steam32_id = [];
                                            if (is_array($game->scoreboard->dire->players->player)) {
                                                foreach ($game->scoreboard->dire->players->player as $player_idx => $player) {
                                                    $dire_player = new Dota2LiveMatchPlayer([
                                                        'steam32_id' => $player->account_id,
                                                        'name' => $players[$player->account_id],
                                                        'kills' => $player->kills,
                                                        'death' => $player->death,
                                                        'assists' => $player->assists,
                                                        'last_hits' => $player->last_hits,
                                                        'denies' => $player->denies,
                                                        'gold' => $player->gold,
                                                        'level' => $player->level,
                                                        'gold_per_min' => $player->gold_per_min,
                                                        'xp_per_min' => $player->xp_per_min,
                                                        'respawn_timer' => $player->respawn_timer,
                                                        'position_x' => $player->position_x,
                                                        'position_y' => $player->position_y,
                                                        'net_worth' => $player->net_worth,
                                                        'player_order' => $player->player_slot
                                                    ]);
                                                    if ($player->hero_id != 0) {
                                                        $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                    }
                                                    $dire->dota2_live_match_players()->save($dire_player);

                                                    for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                        if ($player->{'item'.$item_idx} != 0) {
                                                            $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                                'item_order' => $item_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }

                                                    $last_gold = 625;
                                                    $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                        'gold_per_min' => $player->gold_per_min,
                                                        'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                        'net_worth' => $player->net_worth,
                                                        'duration' => $duration
                                                    ]);
                                                    $dire_player->golds()->save($dire_player_gold);

                                                    $last_xp = 0;
                                                    $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                        'xp_per_min' => $player->xp_per_min,
                                                        'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                        'duration' => $duration
                                                    ]);
                                                    $dire_player->xps()->save($dire_player_xp);

                                                    $dire_players[$player_idx] = $dire_player;
                                                    array_push($dire_players_steam32_id, $player->account_id);
                                                    if ($player->hero_id != 0) {
                                                        $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                    }
                                                }
                                            } else {
                                                $player_idx = 0;
                                                $player = $game->scoreboard->dire->players->player;

                                                $dire_player = new Dota2LiveMatchPlayer([
                                                    'steam32_id' => $player->account_id,
                                                    'name' => $players[$player->account_id],
                                                    'kills' => $player->kills,
                                                    'death' => $player->death,
                                                    'assists' => $player->assists,
                                                    'last_hits' => $player->last_hits,
                                                    'denies' => $player->denies,
                                                    'gold' => $player->gold,
                                                    'level' => $player->level,
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'respawn_timer' => $player->respawn_timer,
                                                    'position_x' => $player->position_x,
                                                    'position_y' => $player->position_y,
                                                    'net_worth' => $player->net_worth,
                                                    'player_order' => $player->player_slot
                                                ]);
                                                if ($player->hero_id != 0) {
                                                    $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                }
                                                $dire->dota2_live_match_players()->save($dire_player);

                                                for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                    if ($player->{'item'.$item_idx} != 0) {
                                                        $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                            'item_order' => $item_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }

                                                $last_gold = 625;
                                                $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                    'net_worth' => $player->net_worth,
                                                    'duration' => $duration
                                                ]);
                                                $dire_player->golds()->save($dire_player_gold);

                                                $last_xp = 0;
                                                $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                    'duration' => $duration
                                                ]);
                                                $dire_player->xps()->save($dire_player_xp);

                                                $dire_players[$player_idx] = $dire_player;
                                                array_push($dire_players_steam32_id, $player->account_id);
                                                if ($player->hero_id != 0) {
                                                    $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                }
                                            }

                                            if (isset($game->scoreboard->dire->abilities)) {
                                                if (is_array($game->scoreboard->dire->abilities)) {
                                                    if (count($game->scoreboard->dire->abilities) < count($dire_players)) {
                                                        foreach ($game->scoreboard->dire->abilities as $abilities) {
                                                            $current_player = null;
                                                            $current_player_hero = null;
                                                            $current_player_abilities = [];
                                                            $ability_order = count($current_player_abilities) + 1;

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if (!$current_player) {
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                            if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                                if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                    $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                    $current_player_abilities = $current_player->abilities;
                                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if ($current_player) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);
                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                            if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);
                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else if (count($game->scoreboard->dire->abilities) == count($dire_players)) {
                                                        foreach ($game->scoreboard->dire->abilities as $abilities_idx => $abilities) {
                                                            $current_player = $dire_players[$abilities_idx];
                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                            $current_player_abilities = $current_player->abilities;
                                                            $ability_order = count($current_player_abilities) + 1;
                                                            $current_player_abilities = $current_player_abilities->groupBy('id');

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);
                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        // Dota 2 API Data must be wrong.
                                                    }
                                                } else {
                                                    $abilities = $game->scoreboard->dire->abilities;

                                                    $current_player = null;
                                                    $current_player_hero = null;
                                                    $current_player_abilities = [];
                                                    $ability_order = count($current_player_abilities) + 1;

                                                    if (is_array($abilities->ability)) {
                                                        foreach ($abilities->ability as $ability) {
                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                        if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                            $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $ability = $abilities->ability;

                                                        if (!$current_player) {
                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                    if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                        $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                        $current_player_abilities = $current_player->abilities;
                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($current_player) {
                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            foreach ($leagues[$game->league_id] as $match_id => $participants) {
                                                $radiant_tournament_registration_id = null;
                                                $dire_tournament_registration_id = null;
                                                foreach ($participants as $tournament_registration_id => $participant) {
                                                    if (!$radiant_tournament_registration_id) {
                                                        if (!array_diff($participant->steam32_id, $radiant_players_steam32_id)) {
                                                            $radiant_tournament_registration_id = $tournament_registration_id;
                                                            continue;
                                                        }
                                                    }

                                                    if (!$dire_tournament_registration_id) {
                                                        if (!array_diff($participant->steam32_id, $dire_players_steam32_id)) {
                                                            $dire_tournament_registration_id = $tournament_registration_id;
                                                            continue;
                                                        }
                                                    }

                                                    if ($radiant_tournament_registration_id && $dire_tournament_registration_id) {
                                                        break;
                                                    }
                                                }

                                                if ($radiant_tournament_registration_id && $dire_tournament_registration_id) {
                                                    $dota2_live_match->match()->associate(Match::find($match_id));
                                                    $dota2_live_match->save();

                                                    $radiant->tournament_registration()->associate(TournamentRegistration::find($radiant_tournament_registration_id));
                                                    $radiant->save();
                                                    foreach ($radiant_players as $radiant_player) {
                                                        $radiant_player->member()->associate(Member::find($participants[$radiant_tournament_registration_id]->steam32_id_indicator[$radiant_player->steam32_id]));
                                                        $radiant_player->save();
                                                    }

                                                    $dire->tournament_registration()->associate(TournamentRegistration::find($dire_tournament_registration_id));
                                                    $dire->save();
                                                    foreach ($dire_players as $dire_player) {
                                                        $dire_player->member()->associate(Member::find($participants[$dire_tournament_registration_id]->steam32_id_indicator[$dire_player->steam32_id]));
                                                        $dire_player->save();
                                                    }

                                                    break;
                                                }
                                            }

                                            DB::commit();
                                        } catch (\Exception $e) {
                                            DB::rollBack();
                                            // dd($e->getMessage());
                                        }
                                    }
                                }
                            }
                        } else {
                            $game = $dota2_live_league_games->games->game;
                            if (array_key_exists($game->league_id, $leagues)) {
                                $players = [];
                                foreach ($game->players->player as $player) {
                                    $players[$player->account_id] = $player->name;
                                }
                                $duration = (int) ceil($game->scoreboard->duration);

                                $dota2_live_match = Dota2LiveMatch::find($game->match_id);
                                if ($dota2_live_match) {
                                    if (isset($dota2_live_matches->{$game->match_id})) {
                                        unset($dota2_live_matches->{$game->match_id});
                                    }

                                    $old_duration = $dota2_live_match->duration;
                                    $timelapse = $duration - $old_duration;

                                    DB::beginTransaction();
                                    try {
                                        $dota2_live_match->spectators = $game->spectators;
                                        $dota2_live_match->duration = $duration;
                                        $dota2_live_match->roshan_respawn_timer = $game->scoreboard->roshan_respawn_timer;
                                        $dota2_live_match->save();

                                        if ($timelapse > 0) {
                                            $dota2_live_match_duration_log = new Dota2LiveMatchDurationLog([
                                                'duration' => $duration
                                            ]);
                                            $dota2_live_match->durations()->save($dota2_live_match_duration_log);
                                        }

                                        $radiant = $dota2_live_match->dota2_live_match_teams()->where('side', 1)->first();
                                        if ($radiant) {
                                            $radiant->score = $game->scoreboard->radiant->score;
                                            $radiant->tower_state = $game->scoreboard->radiant->tower_state;
                                            $radiant->barracks_state = $game->scoreboard->radiant->barracks_state;
                                            $radiant->save();

                                            $pick_amount = $radiant->heroes_pick()->count();
                                            if (isset($game->scoreboard->radiant->picks)) {
                                                if (is_array($game->scoreboard->radiant->picks->pick)) {
                                                    for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->radiant->picks->pick); $pick_idx++) {
                                                        $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick[$pick_idx]->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($pick_amount < 1) {
                                                        $pick_idx = $pick_amount;
                                                        $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $ban_amount = $radiant->heroes_ban()->count();
                                            if (isset($game->scoreboard->radiant->bans)) {
                                                if (is_array($game->scoreboard->radiant->bans->ban)) {
                                                    for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->radiant->bans->ban); $ban_idx++) {
                                                        $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban[$ban_idx]->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($ban_amount < 1) {
                                                        $ban_idx = $ban_amount;
                                                        $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $radiant_players_exists_in_db = $radiant->dota2_live_match_players->groupBy('steam32_id');
                                            $radiant_players = [];
                                            $radiant_players_hero_indicator = [];
                                            $radiant_golds = [];
                                            $radiant_xps = [];
                                            if (is_array($game->scoreboard->radiant->players->player)) {
                                                foreach ($game->scoreboard->radiant->players->player as $player_idx => $player) {
                                                    $radiant_player = null;
                                                    if (isset($radiant_players_exists_in_db[$player->account_id])) {
                                                        $radiant_player = $radiant_players_exists_in_db[$player->account_id][0];
                                                    }

                                                    if ($radiant_player) {
                                                        $radiant_player->kills = $player->kills;
                                                        $radiant_player->death = $player->death;
                                                        $radiant_player->assists = $player->assists;
                                                        $radiant_player->last_hits = $player->last_hits;
                                                        $radiant_player->denies = $player->denies;
                                                        $radiant_player->gold = $player->gold;
                                                        $radiant_player->level = $player->level;
                                                        $radiant_player->gold_per_min = $player->gold_per_min;
                                                        $radiant_player->xp_per_min = $player->xp_per_min;
                                                        $radiant_player->respawn_timer = $player->respawn_timer;
                                                        $radiant_player->position_x = $player->position_x;
                                                        $radiant_player->position_y = $player->position_y;
                                                        $radiant_player->net_worth = $player->net_worth;
                                                        if ($player->hero_id != 0) {
                                                            $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                        }
                                                        $radiant_player->save();

                                                        $radiant_player->items()->detach();
                                                        for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                            if ($player->{'item'.$item_idx} != 0) {
                                                                $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                                    'item_order' => $item_idx + 1,
                                                                    'created_at' => Carbon::now(),
                                                                    'updated_at' => Carbon::now()
                                                                ]);
                                                            }
                                                        }

                                                        if ($timelapse > 0) {
                                                            $last_gold = $radiant_player->golds()->where('duration', $old_duration)->first();
                                                            if ($last_gold) {
                                                                $last_gold = $last_gold->gold;
                                                            } else {
                                                                $last_gold = 625;
                                                            }
                                                            $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                                'gold_per_min' => $player->gold_per_min,
                                                                'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                                'net_worth' => $player->net_worth,
                                                                'duration' => $duration
                                                            ]);
                                                            $radiant_player->golds()->save($radiant_player_gold);
                                                            array_push($radiant_golds, (object) [
                                                                'id' => $radiant_player->id,
                                                                'net_worth' => $player->net_worth
                                                            ]);

                                                            $last_xp = $radiant_player->xps()->where('duration', $old_duration)->first();
                                                            if ($last_xp) {
                                                                $last_xp = $last_xp->xp;
                                                            } else {
                                                                $last_xp = 0;
                                                            }
                                                            $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                                'xp_per_min' => $player->xp_per_min,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                                'duration' => $duration
                                                            ]);
                                                            $radiant_player->xps()->save($radiant_player_xp);
                                                            array_push($radiant_xps, (object) [
                                                                'id' => $radiant_player->id,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                            ]);
                                                        }

                                                        $radiant_players[$player_idx] = $radiant_player;
                                                        if ($player->hero_id != 0) {
                                                            $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                        }
                                                    }
                                                }
                                            } else {
                                                $player_idx = 0;
                                                $player = $game->scoreboard->radiant->players->player;

                                                $radiant_player = null;
                                                if (isset($radiant_players_exists_in_db[$player->account_id])) {
                                                    $radiant_player = $radiant_players_exists_in_db[$player->account_id][0];
                                                }

                                                if ($radiant_player) {
                                                    $radiant_player->kills = $player->kills;
                                                    $radiant_player->death = $player->death;
                                                    $radiant_player->assists = $player->assists;
                                                    $radiant_player->last_hits = $player->last_hits;
                                                    $radiant_player->denies = $player->denies;
                                                    $radiant_player->gold = $player->gold;
                                                    $radiant_player->level = $player->level;
                                                    $radiant_player->gold_per_min = $player->gold_per_min;
                                                    $radiant_player->xp_per_min = $player->xp_per_min;
                                                    $radiant_player->respawn_timer = $player->respawn_timer;
                                                    $radiant_player->position_x = $player->position_x;
                                                    $radiant_player->position_y = $player->position_y;
                                                    $radiant_player->net_worth = $player->net_worth;
                                                    if ($player->hero_id != 0) {
                                                        $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                    }
                                                    $radiant_player->save();

                                                    $radiant_player->items()->detach();
                                                    for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                        if ($player->{'item'.$item_idx} != 0) {
                                                            $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                                'item_order' => $item_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }

                                                    if ($timelapse > 0) {
                                                        $last_gold = $radiant_player->golds()->where('duration', $old_duration)->first();
                                                        if ($last_gold) {
                                                            $last_gold = $last_gold->gold;
                                                        } else {
                                                            $last_gold = 625;
                                                        }
                                                        $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                            'gold_per_min' => $player->gold_per_min,
                                                            'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                            'net_worth' => $player->net_worth,
                                                            'duration' => $duration
                                                        ]);
                                                        $radiant_player->golds()->save($radiant_player_gold);
                                                        array_push($radiant_golds, (object) [
                                                            'id' => $radiant_player->id,
                                                            'net_worth' => $player->net_worth
                                                        ]);

                                                        $last_xp = $radiant_player->xps()->where('duration', $old_duration)->first();
                                                        if ($last_xp) {
                                                            $last_xp = $last_xp->xp;
                                                        } else {
                                                            $last_xp = 0;
                                                        }
                                                        $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                            'xp_per_min' => $player->xp_per_min,
                                                            'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                            'duration' => $duration
                                                        ]);
                                                        $radiant_player->xps()->save($radiant_player_xp);
                                                        array_push($radiant_xps, (object) [
                                                            'id' => $radiant_player->id,
                                                            'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                        ]);
                                                    }

                                                    $radiant_players[$player_idx] = $radiant_player;
                                                    if ($player->hero_id != 0) {
                                                        $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                    }
                                                }
                                            }

                                            $radiant_abilities = [];
                                            if (isset($game->scoreboard->radiant->abilities)) {
                                                if (is_array($game->scoreboard->radiant->abilities)) {
                                                    if (count($game->scoreboard->radiant->abilities) < count($radiant_players)) {
                                                        foreach ($game->scoreboard->radiant->abilities as $abilities) {
                                                            $current_player = null;
                                                            $current_player_hero = null;
                                                            $current_player_abilities = [];
                                                            $ability_order = count($current_player_abilities) + 1;

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if (!$current_player) {
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                            if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                                if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                    $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                    $current_player_abilities = $current_player->abilities;
                                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if ($current_player) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                            $abilities_detail = null;
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                            }

                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);

                                                                                if ($abilities_detail) {
                                                                                    array_push($radiant_abilities, (object) [
                                                                                        'id' => $current_player->id,
                                                                                        'name' => $abilities_detail->name,
                                                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                        'ability_order' => $ability_order
                                                                                    ]);
                                                                                }

                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                            if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($radiant_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else if (count($game->scoreboard->radiant->abilities) == count($radiant_players)) {
                                                        foreach ($game->scoreboard->radiant->abilities as $abilities_idx => $abilities) {
                                                            $current_player = $radiant_players[$abilities_idx];
                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                            $current_player_abilities = $current_player->abilities;
                                                            $ability_order = count($current_player_abilities) + 1;
                                                            $current_player_abilities = $current_player_abilities->groupBy('id');

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($radiant_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                    $abilities_detail = null;
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                    }

                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);

                                                                        if ($abilities_detail) {
                                                                            array_push($radiant_abilities, (object) [
                                                                                'id' => $current_player->id,
                                                                                'name' => $abilities_detail->name,
                                                                                'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                'ability_order' => $ability_order
                                                                            ]);
                                                                        }

                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        // Dota 2 API Data must be wrong.
                                                    }
                                                } else {
                                                    $abilities = $game->scoreboard->radiant->abilities;

                                                    $current_player = null;
                                                    $current_player_hero = null;
                                                    $current_player_abilities = [];
                                                    $ability_order = count($current_player_abilities) + 1;

                                                    if (is_array($abilities->ability)) {
                                                        foreach ($abilities->ability as $ability) {
                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                        if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                            $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                    $abilities_detail = null;
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                    }

                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);

                                                                        if ($abilities_detail) {
                                                                            array_push($radiant_abilities, (object) [
                                                                                'id' => $current_player->id,
                                                                                'name' => $abilities_detail->name,
                                                                                'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                'ability_order' => $ability_order
                                                                            ]);
                                                                        }

                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $ability = $abilities->ability;

                                                        if (!$current_player) {
                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                    if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                        $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                        $current_player_abilities = $current_player->abilities;
                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($current_player) {
                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                $abilities_detail = null;
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                }

                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);

                                                                    if ($abilities_detail) {
                                                                        array_push($radiant_abilities, (object) [
                                                                            'id' => $current_player->id,
                                                                            'name' => $abilities_detail->name,
                                                                            'picture_file_name' => $abilities_detail->picture_file_name,
                                                                            'ability_order' => $ability_order
                                                                        ]);
                                                                    }

                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $dire = $dota2_live_match->dota2_live_match_teams()->where('side', 2)->first();
                                        if ($dire) {
                                            $dire->score = $game->scoreboard->dire->score;
                                            $dire->tower_state = $game->scoreboard->dire->tower_state;
                                            $dire->barracks_state = $game->scoreboard->dire->barracks_state;
                                            $dire->save();

                                            $pick_amount = $dire->heroes_pick()->count();
                                            if (isset($game->scoreboard->dire->picks)) {
                                                if (is_array($game->scoreboard->dire->picks->pick)) {
                                                    for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->dire->picks->pick); $pick_idx++) {
                                                        $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick[$pick_idx]->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($pick_amount < 1) {
                                                        $pick_idx = $pick_amount;
                                                        $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick->hero_id, [
                                                            'pick_order' => $pick_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $ban_amount = $dire->heroes_ban()->count();
                                            if (isset($game->scoreboard->dire->bans)) {
                                                if (is_array($game->scoreboard->dire->bans->ban)) {
                                                    for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->dire->bans->ban); $ban_idx++) {
                                                        $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban[$ban_idx]->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                } else {
                                                    if ($ban_amount < 1) {
                                                        $ban_idx = $ban_amount;
                                                        $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban->hero_id, [
                                                            'ban_order' => $ban_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }
                                            }

                                            $dire_players_exists_in_db = $dire->dota2_live_match_players->groupBy('steam32_id');
                                            $dire_players = [];
                                            $dire_players_hero_indicator = [];
                                            $dire_golds = [];
                                            $dire_xps = [];
                                            if (is_array($game->scoreboard->dire->players->player)) {
                                                foreach ($game->scoreboard->dire->players->player as $player_idx => $player) {
                                                    $dire_player = null;
                                                    if (isset($dire_players_exists_in_db[$player->account_id])) {
                                                        $dire_player = $dire_players_exists_in_db[$player->account_id][0];
                                                    }

                                                    if ($dire_player) {
                                                        $dire_player->kills = $player->kills;
                                                        $dire_player->death = $player->death;
                                                        $dire_player->assists = $player->assists;
                                                        $dire_player->last_hits = $player->last_hits;
                                                        $dire_player->denies = $player->denies;
                                                        $dire_player->gold = $player->gold;
                                                        $dire_player->level = $player->level;
                                                        $dire_player->gold_per_min = $player->gold_per_min;
                                                        $dire_player->xp_per_min = $player->xp_per_min;
                                                        $dire_player->respawn_timer = $player->respawn_timer;
                                                        $dire_player->position_x = $player->position_x;
                                                        $dire_player->position_y = $player->position_y;
                                                        $dire_player->net_worth = $player->net_worth;
                                                        if ($player->hero_id != 0) {
                                                            $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                        }
                                                        $dire_player->save();

                                                        $dire_player->items()->detach();
                                                        for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                            if ($player->{'item'.$item_idx} != 0) {
                                                                $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                                    'item_order' => $item_idx + 1,
                                                                    'created_at' => Carbon::now(),
                                                                    'updated_at' => Carbon::now()
                                                                ]);
                                                            }
                                                        }

                                                        if ($timelapse > 0) {
                                                            $last_gold = $dire_player->golds()->where('duration', $old_duration)->first();
                                                            if ($last_gold) {
                                                                $last_gold = $last_gold->gold;
                                                            } else {
                                                                $last_gold = 625;
                                                            }
                                                            $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                                'gold_per_min' => $player->gold_per_min,
                                                                'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                                'net_worth' => $player->net_worth,
                                                                'duration' => $duration
                                                            ]);
                                                            $dire_player->golds()->save($dire_player_gold);
                                                            array_push($dire_golds, (object) [
                                                                'id' => $dire_player->id,
                                                                'net_worth' => $player->net_worth
                                                            ]);

                                                            $last_xp = $dire_player->xps()->where('duration', $old_duration)->first();
                                                            if ($last_xp) {
                                                                $last_xp = $last_xp->xp;
                                                            } else {
                                                                $last_xp = 0;
                                                            }
                                                            $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                                'xp_per_min' => $player->xp_per_min,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                                'duration' => $duration
                                                            ]);
                                                            $dire_player->xps()->save($dire_player_xp);
                                                            array_push($dire_xps, (object) [
                                                                'id' => $dire_player->id,
                                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                            ]);
                                                        }

                                                        $dire_players[$player_idx] = $dire_player;
                                                        if ($player->hero_id != 0) {
                                                            $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                        }
                                                    }
                                                }
                                            } else {
                                                $player_idx = 0;
                                                $player = $game->scoreboard->dire->players->player;

                                                $dire_player = null;
                                                if (isset($dire_players_exists_in_db[$player->account_id])) {
                                                    $dire_player = $dire_players_exists_in_db[$player->account_id][0];
                                                }

                                                if ($dire_player) {
                                                    $dire_player->kills = $player->kills;
                                                    $dire_player->death = $player->death;
                                                    $dire_player->assists = $player->assists;
                                                    $dire_player->last_hits = $player->last_hits;
                                                    $dire_player->denies = $player->denies;
                                                    $dire_player->gold = $player->gold;
                                                    $dire_player->level = $player->level;
                                                    $dire_player->gold_per_min = $player->gold_per_min;
                                                    $dire_player->xp_per_min = $player->xp_per_min;
                                                    $dire_player->respawn_timer = $player->respawn_timer;
                                                    $dire_player->position_x = $player->position_x;
                                                    $dire_player->position_y = $player->position_y;
                                                    $dire_player->net_worth = $player->net_worth;
                                                    if ($player->hero_id != 0) {
                                                        $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                    }
                                                    $dire_player->save();

                                                    $dire_player->items()->detach();
                                                    for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                        if ($player->{'item'.$item_idx} != 0) {
                                                            $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                                'item_order' => $item_idx + 1,
                                                                'created_at' => Carbon::now(),
                                                                'updated_at' => Carbon::now()
                                                            ]);
                                                        }
                                                    }

                                                    if ($timelapse > 0) {
                                                        $last_gold = $dire_player->golds()->where('duration', $old_duration)->first();
                                                        if ($last_gold) {
                                                            $last_gold = $last_gold->gold;
                                                        } else {
                                                            $last_gold = 625;
                                                        }
                                                        $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                            'gold_per_min' => $player->gold_per_min,
                                                            'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                            'net_worth' => $player->net_worth,
                                                            'duration' => $duration
                                                        ]);
                                                        $dire_player->golds()->save($dire_player_gold);
                                                        array_push($dire_golds, (object) [
                                                            'id' => $dire_player->id,
                                                            'net_worth' => $player->net_worth
                                                        ]);

                                                        $last_xp = $dire_player->xps()->where('duration', $old_duration)->first();
                                                        if ($last_xp) {
                                                            $last_xp = $last_xp->xp;
                                                        } else {
                                                            $last_xp = 0;
                                                        }
                                                        $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                            'xp_per_min' => $player->xp_per_min,
                                                            'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                            'duration' => $duration
                                                        ]);
                                                        $dire_player->xps()->save($dire_player_xp);
                                                        array_push($dire_xps, (object) [
                                                            'id' => $dire_player->id,
                                                            'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                                        ]);
                                                    }

                                                    $dire_players[$player_idx] = $dire_player;
                                                    if ($player->hero_id != 0) {
                                                        $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                    }
                                                }
                                            }

                                            $dire_abilities = [];
                                            if (isset($game->scoreboard->dire->abilities)) {
                                                if (is_array($game->scoreboard->dire->abilities)) {
                                                    if (count($game->scoreboard->dire->abilities) < count($dire_players)) {
                                                        foreach ($game->scoreboard->dire->abilities as $abilities) {
                                                            $current_player = null;
                                                            $current_player_hero = null;
                                                            $current_player_abilities = [];
                                                            $ability_order = count($current_player_abilities) + 1;

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if (!$current_player) {
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                            if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                                if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                    $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                    $current_player_abilities = $current_player->abilities;
                                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    if ($current_player) {
                                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                            $abilities_detail = null;
                                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                                $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                            }

                                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                                    'ability_order' => $ability_order,
                                                                                    'created_at' => Carbon::now(),
                                                                                    'updated_at' => Carbon::now()
                                                                                ]);

                                                                                if ($abilities_detail) {
                                                                                    array_push($dire_abilities, (object) [
                                                                                        'id' => $current_player->id,
                                                                                        'name' => $abilities_detail->name,
                                                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                        'ability_order' => $ability_order
                                                                                    ]);
                                                                                }

                                                                                $ability_order++;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                            if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($dire_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else if (count($game->scoreboard->dire->abilities) == count($dire_players)) {
                                                        foreach ($game->scoreboard->dire->abilities as $abilities_idx => $abilities) {
                                                            $current_player = $dire_players[$abilities_idx];
                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                            $current_player_abilities = $current_player->abilities;
                                                            $ability_order = count($current_player_abilities) + 1;
                                                            $current_player_abilities = $current_player_abilities->groupBy('id');

                                                            if (is_array($abilities->ability)) {
                                                                foreach ($abilities->ability as $ability) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                        $abilities_detail = null;
                                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                                            $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                        }

                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);

                                                                            if ($abilities_detail) {
                                                                                array_push($dire_abilities, (object) [
                                                                                    'id' => $current_player->id,
                                                                                    'name' => $abilities_detail->name,
                                                                                    'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                    'ability_order' => $ability_order
                                                                                ]);
                                                                            }

                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            } else {
                                                                $ability = $abilities->ability;

                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                    $abilities_detail = null;
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                    }

                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);

                                                                        if ($abilities_detail) {
                                                                            array_push($dire_abilities, (object) [
                                                                                'id' => $current_player->id,
                                                                                'name' => $abilities_detail->name,
                                                                                'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                'ability_order' => $ability_order
                                                                            ]);
                                                                        }

                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        // Dota 2 API Data must be wrong.
                                                    }
                                                } else {
                                                    $abilities = $game->scoreboard->dire->abilities;

                                                    $current_player = null;
                                                    $current_player_hero = null;
                                                    $current_player_abilities = [];
                                                    $ability_order = count($current_player_abilities) + 1;

                                                    if (is_array($abilities->ability)) {
                                                        foreach ($abilities->ability as $ability) {
                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                        if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                            $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                    $abilities_detail = null;
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                    }

                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);

                                                                        if ($abilities_detail) {
                                                                            array_push($dire_abilities, (object) [
                                                                                'id' => $current_player->id,
                                                                                'name' => $abilities_detail->name,
                                                                                'picture_file_name' => $abilities_detail->picture_file_name,
                                                                                'ability_order' => $ability_order
                                                                            ]);
                                                                        }

                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $ability = $abilities->ability;

                                                        if (!$current_player) {
                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                    if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                        $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                        $current_player_abilities = $current_player->abilities;
                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($current_player) {
                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;

                                                                $abilities_detail = null;
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $abilities_detail = $dota2_abilities[$ability->ability_id]->first();
                                                                }

                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);

                                                                    if ($abilities_detail) {
                                                                        array_push($dire_abilities, (object) [
                                                                            'id' => $current_player->id,
                                                                            'name' => $abilities_detail->name,
                                                                            'picture_file_name' => $abilities_detail->picture_file_name,
                                                                            'ability_order' => $ability_order
                                                                        ]);
                                                                    }

                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        DB::commit();

                                        event(new Dota2LiveMatchUpdated($dota2_live_match, $radiant_golds, $radiant_xps, $dire_golds, $dire_xps));
                                        event(new Dota2LiveMatchPlayersItemsUpdated($dota2_live_match));
                                        event(new Dota2LiveMatchRadiantPlayersUpdated($dota2_live_match, $radiant_abilities));
                                        event(new Dota2LiveMatchDirePlayersUpdated($dota2_live_match, $dire_abilities));
                                    } catch (\Exception $e) {
                                        DB::rollBack();
                                        // dd($e->getMessage());
                                    }
                                } else {
                                    $timelapse = $duration;

                                    DB::beginTransaction();
                                    try {
                                        $dota2_live_match = new Dota2LiveMatch([
                                            'id' => $game->match_id,
                                            'leagues_id' => $game->league_id,
                                            'series_type' => $game->series_type,
                                            'spectators' => $game->spectators,
                                            'duration' => $duration,
                                            'roshan_respawn_timer' => $game->scoreboard->roshan_respawn_timer
                                        ]);
                                        $dota2_live_match->save();

                                        $dota2_live_match_duration_log = new Dota2LiveMatchDurationLog([
                                            'duration' => $duration
                                        ]);
                                        $dota2_live_match->durations()->save($dota2_live_match_duration_log);

                                        $radiant = new Dota2LiveMatchTeam([
                                            'dota2_teams_id' => isset($game->radiant_team) ? $game->radiant_team->team_id : null,
                                            'dota2_teams_name' => isset($game->radiant_team) ? $game->radiant_team->team_name : null,
                                            'dota2_teams_logo' => isset($game->radiant_team) ? $game->radiant_team->team_logo : null,
                                            'series_wins' => $game->radiant_series_wins,
                                            'score' => $game->scoreboard->radiant->score,
                                            'tower_state' => $game->scoreboard->radiant->tower_state,
                                            'barracks_state' => $game->scoreboard->radiant->barracks_state,
                                            'side' => 1
                                        ]);
                                        $dota2_live_match->dota2_live_match_teams()->save($radiant);

                                        $pick_amount = 0;
                                        if (isset($game->scoreboard->radiant->picks)) {
                                            if (is_array($game->scoreboard->radiant->picks->pick)) {
                                                for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->radiant->picks->pick); $pick_idx++) {
                                                    $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick[$pick_idx]->hero_id, [
                                                        'pick_order' => $pick_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            } else {
                                                if ($pick_amount < 1) {
                                                    $pick_idx = $pick_amount;
                                                    $radiant->heroes_pick()->attach($game->scoreboard->radiant->picks->pick->hero_id, [
                                                        'pick_order' => $pick_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            }
                                        }

                                        $ban_amount = 0;
                                        if (isset($game->scoreboard->radiant->bans)) {
                                            if (is_array($game->scoreboard->radiant->bans->ban)) {
                                                for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->radiant->bans->ban); $ban_idx++) {
                                                    $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban[$ban_idx]->hero_id, [
                                                        'ban_order' => $ban_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            } else {
                                                if ($ban_amount < 1) {
                                                    $ban_idx = $ban_amount;
                                                    $radiant->heroes_ban()->attach($game->scoreboard->radiant->bans->ban->hero_id, [
                                                        'ban_order' => $ban_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            }
                                        }

                                        $radiant_players = [];
                                        $radiant_players_hero_indicator = [];
                                        $radiant_players_steam32_id = [];
                                        if (is_array($game->scoreboard->radiant->players->player)) {
                                            foreach ($game->scoreboard->radiant->players->player as $player_idx => $player) {
                                                $radiant_player = new Dota2LiveMatchPlayer([
                                                    'steam32_id' => $player->account_id,
                                                    'name' => $players[$player->account_id],
                                                    'kills' => $player->kills,
                                                    'death' => $player->death,
                                                    'assists' => $player->assists,
                                                    'last_hits' => $player->last_hits,
                                                    'denies' => $player->denies,
                                                    'gold' => $player->gold,
                                                    'level' => $player->level,
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'respawn_timer' => $player->respawn_timer,
                                                    'position_x' => $player->position_x,
                                                    'position_y' => $player->position_y,
                                                    'net_worth' => $player->net_worth,
                                                    'player_order' => $player->player_slot
                                                ]);
                                                if ($player->hero_id != 0) {
                                                    $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                }
                                                $radiant->dota2_live_match_players()->save($radiant_player);

                                                for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                    if ($player->{'item'.$item_idx} != 0) {
                                                        $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                            'item_order' => $item_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }

                                                $last_gold = 625;
                                                $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                    'net_worth' => $player->net_worth,
                                                    'duration' => $duration
                                                ]);
                                                $radiant_player->golds()->save($radiant_player_gold);

                                                $last_xp = 0;
                                                $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                    'duration' => $duration
                                                ]);
                                                $radiant_player->xps()->save($radiant_player_xp);

                                                $radiant_players[$player_idx] = $radiant_player;
                                                array_push($radiant_players_steam32_id, $player->account_id);
                                                if ($player->hero_id != 0) {
                                                    $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                }
                                            }
                                        } else {
                                            $player_idx = 0;
                                            $player = $game->scoreboard->radiant->players->player;

                                            $radiant_player = new Dota2LiveMatchPlayer([
                                                'steam32_id' => $player->account_id,
                                                'name' => $players[$player->account_id],
                                                'kills' => $player->kills,
                                                'death' => $player->death,
                                                'assists' => $player->assists,
                                                'last_hits' => $player->last_hits,
                                                'denies' => $player->denies,
                                                'gold' => $player->gold,
                                                'level' => $player->level,
                                                'gold_per_min' => $player->gold_per_min,
                                                'xp_per_min' => $player->xp_per_min,
                                                'respawn_timer' => $player->respawn_timer,
                                                'position_x' => $player->position_x,
                                                'position_y' => $player->position_y,
                                                'net_worth' => $player->net_worth,
                                                'player_order' => $player->player_slot
                                            ]);
                                            if ($player->hero_id != 0) {
                                                $radiant_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                            }
                                            $radiant->dota2_live_match_players()->save($radiant_player);

                                            for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                if ($player->{'item'.$item_idx} != 0) {
                                                    $radiant_player->items()->attach($player->{'item'.$item_idx}, [
                                                        'item_order' => $item_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            }

                                            $last_gold = 625;
                                            $radiant_player_gold = new Dota2LiveMatchPlayerGold([
                                                'gold_per_min' => $player->gold_per_min,
                                                'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                'net_worth' => $player->net_worth,
                                                'duration' => $duration
                                            ]);
                                            $radiant_player->golds()->save($radiant_player_gold);

                                            $last_xp = 0;
                                            $radiant_player_xp = new Dota2LiveMatchPlayerXP([
                                                'xp_per_min' => $player->xp_per_min,
                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                'duration' => $duration
                                            ]);
                                            $radiant_player->xps()->save($radiant_player_xp);

                                            $radiant_players[$player_idx] = $radiant_player;
                                            array_push($radiant_players_steam32_id, $player->account_id);
                                            if ($player->hero_id != 0) {
                                                $radiant_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                            }
                                        }

                                        if (isset($game->scoreboard->radiant->abilities)) {
                                            if (is_array($game->scoreboard->radiant->abilities)) {
                                                if (count($game->scoreboard->radiant->abilities) < count($radiant_players)) {
                                                    foreach ($game->scoreboard->radiant->abilities as $abilities) {
                                                        $current_player = null;
                                                        $current_player_hero = null;
                                                        $current_player_abilities = [];
                                                        $ability_order = count($current_player_abilities) + 1;

                                                        if (is_array($abilities->ability)) {
                                                            foreach ($abilities->ability as $ability) {
                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                            if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                                $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);
                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $ability = $abilities->ability;

                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                        if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                            $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else if (count($game->scoreboard->radiant->abilities) == count($radiant_players)) {
                                                    foreach ($game->scoreboard->radiant->abilities as $abilities_idx => $abilities) {
                                                        $current_player = $radiant_players[$abilities_idx];
                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                        $current_player_abilities = $current_player->abilities;
                                                        $ability_order = count($current_player_abilities) + 1;
                                                        $current_player_abilities = $current_player_abilities->groupBy('id');

                                                        if (is_array($abilities->ability)) {
                                                            foreach ($abilities->ability as $ability) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $ability = $abilities->ability;

                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    // Dota 2 API Data must be wrong.
                                                }
                                            } else {
                                                $abilities = $game->scoreboard->radiant->abilities;

                                                $current_player = null;
                                                $current_player_hero = null;
                                                $current_player_abilities = [];
                                                $ability_order = count($current_player_abilities) + 1;

                                                if (is_array($abilities->ability)) {
                                                    foreach ($abilities->ability as $ability) {
                                                        if (!$current_player) {
                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                    if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                        $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                        $current_player_abilities = $current_player->abilities;
                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($current_player) {
                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $ability = $abilities->ability;

                                                    if (!$current_player) {
                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                            if (array_key_exists($dota2_hero->id, $radiant_players_hero_indicator)) {
                                                                if (array_key_exists($radiant_players_hero_indicator[$dota2_hero->id], $radiant_players)) {
                                                                    $current_player = $radiant_players[$radiant_players_hero_indicator[$dota2_hero->id]];
                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                    $current_player_abilities = $current_player->abilities;
                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                }
                                                            }
                                                        }
                                                    }

                                                    if ($current_player) {
                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                    'ability_order' => $ability_order,
                                                                    'created_at' => Carbon::now(),
                                                                    'updated_at' => Carbon::now()
                                                                ]);
                                                                $ability_order++;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $dire = new Dota2LiveMatchTeam([
                                            'dota2_teams_id' => isset($game->dire_team) ? $game->dire_team->team_id : null,
                                            'dota2_teams_name' => isset($game->dire_team) ? $game->dire_team->team_name : null,
                                            'dota2_teams_logo' => isset($game->dire_team) ? $game->dire_team->team_logo : null,
                                            'series_wins' => $game->dire_series_wins,
                                            'score' => $game->scoreboard->dire->score,
                                            'tower_state' => $game->scoreboard->dire->tower_state,
                                            'barracks_state' => $game->scoreboard->dire->barracks_state,
                                            'side' => 2
                                        ]);
                                        $dota2_live_match->dota2_live_match_teams()->save($dire);

                                        $pick_amount = 0;
                                        if (isset($game->scoreboard->dire->picks)) {
                                            if (is_array($game->scoreboard->dire->picks->pick)) {
                                                for ($pick_idx = $pick_amount; $pick_idx < count($game->scoreboard->dire->picks->pick); $pick_idx++) {
                                                    $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick[$pick_idx]->hero_id, [
                                                        'pick_order' => $pick_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            } else {
                                                if ($pick_amount < 1) {
                                                    $pick_idx = $pick_amount;
                                                    $dire->heroes_pick()->attach($game->scoreboard->dire->picks->pick->hero_id, [
                                                        'pick_order' => $pick_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            }
                                        }

                                        $ban_amount = 0;
                                        if (isset($game->scoreboard->dire->bans)) {
                                            if (is_array($game->scoreboard->dire->bans->ban)) {
                                                for ($ban_idx = $ban_amount; $ban_idx < count($game->scoreboard->dire->bans->ban); $ban_idx++) {
                                                    $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban[$ban_idx]->hero_id, [
                                                        'ban_order' => $ban_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            } else {
                                                if ($ban_amount < 1) {
                                                    $ban_idx = $ban_amount;
                                                    $dire->heroes_ban()->attach($game->scoreboard->dire->bans->ban->hero_id, [
                                                        'ban_order' => $ban_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            }
                                        }

                                        $dire_players = [];
                                        $dire_players_hero_indicator = [];
                                        $dire_players_steam32_id = [];
                                        if (is_array($game->scoreboard->dire->players->player)) {
                                            foreach ($game->scoreboard->dire->players->player as $player_idx => $player) {
                                                $dire_player = new Dota2LiveMatchPlayer([
                                                    'steam32_id' => $player->account_id,
                                                    'name' => $players[$player->account_id],
                                                    'kills' => $player->kills,
                                                    'death' => $player->death,
                                                    'assists' => $player->assists,
                                                    'last_hits' => $player->last_hits,
                                                    'denies' => $player->denies,
                                                    'gold' => $player->gold,
                                                    'level' => $player->level,
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'respawn_timer' => $player->respawn_timer,
                                                    'position_x' => $player->position_x,
                                                    'position_y' => $player->position_y,
                                                    'net_worth' => $player->net_worth,
                                                    'player_order' => $player->player_slot
                                                ]);
                                                if ($player->hero_id != 0) {
                                                    $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                                }
                                                $dire->dota2_live_match_players()->save($dire_player);

                                                for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                    if ($player->{'item'.$item_idx} != 0) {
                                                        $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                            'item_order' => $item_idx + 1,
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => Carbon::now()
                                                        ]);
                                                    }
                                                }

                                                $last_gold = 625;
                                                $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                    'gold_per_min' => $player->gold_per_min,
                                                    'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                    'net_worth' => $player->net_worth,
                                                    'duration' => $duration
                                                ]);
                                                $dire_player->golds()->save($dire_player_gold);

                                                $last_xp = 0;
                                                $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                    'xp_per_min' => $player->xp_per_min,
                                                    'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                    'duration' => $duration
                                                ]);
                                                $dire_player->xps()->save($dire_player_xp);

                                                $dire_players[$player_idx] = $dire_player;
                                                array_push($dire_players_steam32_id, $player->account_id);
                                                if ($player->hero_id != 0) {
                                                    $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                                }
                                            }
                                        } else {
                                            $player_idx = 0;
                                            $player = $game->scoreboard->dire->players->player;

                                            $dire_player = new Dota2LiveMatchPlayer([
                                                'steam32_id' => $player->account_id,
                                                'name' => $players[$player->account_id],
                                                'kills' => $player->kills,
                                                'death' => $player->death,
                                                'assists' => $player->assists,
                                                'last_hits' => $player->last_hits,
                                                'denies' => $player->denies,
                                                'gold' => $player->gold,
                                                'level' => $player->level,
                                                'gold_per_min' => $player->gold_per_min,
                                                'xp_per_min' => $player->xp_per_min,
                                                'respawn_timer' => $player->respawn_timer,
                                                'position_x' => $player->position_x,
                                                'position_y' => $player->position_y,
                                                'net_worth' => $player->net_worth,
                                                'player_order' => $player->player_slot
                                            ]);
                                            if ($player->hero_id != 0) {
                                                $dire_player->hero()->associate(Dota2Hero::find($player->hero_id));
                                            }
                                            $dire->dota2_live_match_players()->save($dire_player);

                                            for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                                if ($player->{'item'.$item_idx} != 0) {
                                                    $dire_player->items()->attach($player->{'item'.$item_idx}, [
                                                        'item_order' => $item_idx + 1,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            }

                                            $last_gold = 625;
                                            $dire_player_gold = new Dota2LiveMatchPlayerGold([
                                                'gold_per_min' => $player->gold_per_min,
                                                'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                                'net_worth' => $player->net_worth,
                                                'duration' => $duration
                                            ]);
                                            $dire_player->golds()->save($dire_player_gold);

                                            $last_xp = 0;
                                            $dire_player_xp = new Dota2LiveMatchPlayerXP([
                                                'xp_per_min' => $player->xp_per_min,
                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                                'duration' => $duration
                                            ]);
                                            $dire_player->xps()->save($dire_player_xp);

                                            $dire_players[$player_idx] = $dire_player;
                                            array_push($dire_players_steam32_id, $player->account_id);
                                            if ($player->hero_id != 0) {
                                                $dire_players_hero_indicator[$player->hero_id] = $player->player_slot - 1;
                                            }
                                        }

                                        if (isset($game->scoreboard->dire->abilities)) {
                                            if (is_array($game->scoreboard->dire->abilities)) {
                                                if (count($game->scoreboard->dire->abilities) < count($dire_players)) {
                                                    foreach ($game->scoreboard->dire->abilities as $abilities) {
                                                        $current_player = null;
                                                        $current_player_hero = null;
                                                        $current_player_abilities = [];
                                                        $ability_order = count($current_player_abilities) + 1;

                                                        if (is_array($abilities->ability)) {
                                                            foreach ($abilities->ability as $ability) {
                                                                if (!$current_player) {
                                                                    if (isset($dota2_abilities[$ability->ability_id])) {
                                                                        $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                        if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                            if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                                $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                                $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                                $current_player_abilities = $current_player->abilities;
                                                                                $ability_order = count($current_player_abilities) + 1;
                                                                                $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                if ($current_player) {
                                                                    if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                        $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                        for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                            $current_player->abilities()->attach($ability->ability_id, [
                                                                                'ability_order' => $ability_order,
                                                                                'created_at' => Carbon::now(),
                                                                                'updated_at' => Carbon::now()
                                                                            ]);
                                                                            $ability_order++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $ability = $abilities->ability;

                                                            if (!$current_player) {
                                                                if (isset($dota2_abilities[$ability->ability_id])) {
                                                                    $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                    if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                        if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                            $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                            $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                            $current_player_abilities = $current_player->abilities;
                                                                            $ability_order = count($current_player_abilities) + 1;
                                                                            $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ($current_player) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else if (count($game->scoreboard->dire->abilities) == count($dire_players)) {
                                                    foreach ($game->scoreboard->dire->abilities as $abilities_idx => $abilities) {
                                                        $current_player = $dire_players[$abilities_idx];
                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                        $current_player_abilities = $current_player->abilities;
                                                        $ability_order = count($current_player_abilities) + 1;
                                                        $current_player_abilities = $current_player_abilities->groupBy('id');

                                                        if (is_array($abilities->ability)) {
                                                            foreach ($abilities->ability as $ability) {
                                                                if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                    $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                    for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                        $current_player->abilities()->attach($ability->ability_id, [
                                                                            'ability_order' => $ability_order,
                                                                            'created_at' => Carbon::now(),
                                                                            'updated_at' => Carbon::now()
                                                                        ]);
                                                                        $ability_order++;
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $ability = $abilities->ability;

                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    // Dota 2 API Data must be wrong.
                                                }
                                            } else {
                                                $abilities = $game->scoreboard->dire->abilities;

                                                $current_player = null;
                                                $current_player_hero = null;
                                                $current_player_abilities = [];
                                                $ability_order = count($current_player_abilities) + 1;

                                                if (is_array($abilities->ability)) {
                                                    foreach ($abilities->ability as $ability) {
                                                        if (!$current_player) {
                                                            if (isset($dota2_abilities[$ability->ability_id])) {
                                                                $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                                if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                    if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                        $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                        $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                        $current_player_abilities = $current_player->abilities;
                                                                        $ability_order = count($current_player_abilities) + 1;
                                                                        $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        if ($current_player) {
                                                            if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                                $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                                for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                    $current_player->abilities()->attach($ability->ability_id, [
                                                                        'ability_order' => $ability_order,
                                                                        'created_at' => Carbon::now(),
                                                                        'updated_at' => Carbon::now()
                                                                    ]);
                                                                    $ability_order++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $ability = $abilities->ability;

                                                    if (!$current_player) {
                                                        if (isset($dota2_abilities[$ability->ability_id])) {
                                                            $dota2_hero = $dota2_abilities[$ability->ability_id]->first()->heroes->first();
                                                            if (array_key_exists($dota2_hero->id, $dire_players_hero_indicator)) {
                                                                if (array_key_exists($dire_players_hero_indicator[$dota2_hero->id], $dire_players)) {
                                                                    $current_player = $dire_players[$dire_players_hero_indicator[$dota2_hero->id]];
                                                                    $current_player_hero = $dota2_heroes[$current_player->dota2_heroes_id]->first();
                                                                    $current_player_abilities = $current_player->abilities;
                                                                    $ability_order = count($current_player_abilities) + 1;
                                                                    $current_player_abilities = $current_player_abilities->groupBy('id');
                                                                }
                                                            }
                                                        }
                                                    }

                                                    if ($current_player) {
                                                        if ($current_player_hero->abilities->contains('id', $ability->ability_id)) {
                                                            $current_ability_level = isset($current_player_abilities[$ability->ability_id]) ? count($current_player_abilities[$ability->ability_id]) : 0;
                                                            for ($ability_level_up = $current_ability_level; $ability_level_up < $ability->ability_level; $ability_level_up++) {
                                                                $current_player->abilities()->attach($ability->ability_id, [
                                                                    'ability_order' => $ability_order,
                                                                    'created_at' => Carbon::now(),
                                                                    'updated_at' => Carbon::now()
                                                                ]);
                                                                $ability_order++;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        foreach ($leagues[$game->league_id] as $match_id => $participants) {
                                            $radiant_tournament_registration_id = null;
                                            $dire_tournament_registration_id = null;
                                            foreach ($participants as $tournament_registration_id => $participant) {
                                                if (!$radiant_tournament_registration_id) {
                                                    if (!array_diff($participant->steam32_id, $radiant_players_steam32_id)) {
                                                        $radiant_tournament_registration_id = $tournament_registration_id;
                                                        continue;
                                                    }
                                                }

                                                if (!$dire_tournament_registration_id) {
                                                    if (!array_diff($participant->steam32_id, $dire_players_steam32_id)) {
                                                        $dire_tournament_registration_id = $tournament_registration_id;
                                                        continue;
                                                    }
                                                }

                                                if ($radiant_tournament_registration_id && $dire_tournament_registration_id) {
                                                    break;
                                                }
                                            }

                                            if ($radiant_tournament_registration_id && $dire_tournament_registration_id) {
                                                $dota2_live_match->match()->associate(Match::find($match_id));
                                                $dota2_live_match->save();

                                                $radiant->tournament_registration()->associate(TournamentRegistration::find($radiant_tournament_registration_id));
                                                $radiant->save();
                                                foreach ($radiant_players as $radiant_player) {
                                                    $radiant_player->member()->associate(Member::find($participants[$radiant_tournament_registration_id]->steam32_id_indicator[$radiant_player->steam32_id]));
                                                    $radiant_player->save();
                                                }

                                                $dire->tournament_registration()->associate(TournamentRegistration::find($dire_tournament_registration_id));
                                                $dire->save();
                                                foreach ($dire_players as $dire_player) {
                                                    $dire_player->member()->associate(Member::find($participants[$dire_tournament_registration_id]->steam32_id_indicator[$dire_player->steam32_id]));
                                                    $dire_player->save();
                                                }

                                                break;
                                            }
                                        }

                                        DB::commit();
                                    } catch (\Exception $e) {
                                        DB::rollBack();
                                        // dd($e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($dota2_live_matches as $dota2_live_matches_id => $dota2_live_matches_parent) {
            $match_details = GuzzleHelper::requestDota2MatchDetails($dota2_live_matches_id);
            if ($match_details) {
                if (!isset($match_details->result->error)) {
                    $duration = $match_details->result->duration;

                    $dota2_live_match = $dota2_live_matches_parent->first();
                    
                    $old_duration = $dota2_live_match->duration;
                    $timelapse = $duration - $old_duration;

                    DB::beginTransaction();
                    try {
                        $dota2_live_match->duration = $duration;
                        $dota2_live_match->save();

                        if ($timelapse > 0) {
                            $dota2_live_match_duration_log = new Dota2LiveMatchDurationLog([
                                'duration' => $duration
                            ]);
                            $dota2_live_match->durations()->save($dota2_live_match_duration_log);
                        }

                        $radiant = $dota2_live_match->dota2_live_match_teams()->where('side', 1)->first();
                        if ($radiant) {
                            $radiant->score = $match_details->result->radiant_score;
                            $radiant->tower_state = $match_details->result->tower_status_radiant;
                            $radiant->barracks_state = $match_details->result->barracks_status_radiant;
                            if ($match_details->result->radiant_win) {
                                $radiant->matches_result = 3;
                            } else {
                                $radiant->matches_result = 1;
                            }
                            $radiant->save();
                        }

                        $dire = $dota2_live_match->dota2_live_match_teams()->where('side', 2)->first();
                        if ($dire) {
                            $dire->score = $match_details->result->dire_score;
                            $dire->tower_state = $match_details->result->tower_status_dire;
                            $dire->barracks_state = $match_details->result->barracks_status_dire;
                            if ($match_details->result->radiant_win) {
                                $dire->matches_result = 1;
                            } else {
                                $dire->matches_result = 3;
                            }
                            $dire->save();
                        }

                        $radiant_golds = [];
                        $radiant_xps = [];
                        $radiant_abilities = [];
                        $dire_golds = [];
                        $dire_xps = [];
                        $dire_abilities = [];
                        if ($radiant && $dire) {
                            $radiant_players = $radiant->dota2_live_match_players->groupBy('steam32_id');
                            $dire_players = $dire->dota2_live_match_players->groupBy('steam32_id');

                            foreach ($match_details->result->players as $player) {
                                $selected_player = null;
                                $is_radiant_player = null;
                                if (isset($radiant_players[$player->account_id])) {
                                    $selected_player = $radiant_players[$player->account_id]->first();
                                    $is_radiant_player = true;
                                }
                                if (isset($dire_players[$player->account_id])) {
                                    $selected_player = $dire_players[$player->account_id]->first();
                                    $is_radiant_player = false;
                                }

                                if ($selected_player) {
                                    $last_net_worth = $selected_player->net_worth;
                                    if ($timelapse > 0) {
                                        $net_worth = $last_net_worth + ($timelapse * $player->gold_per_min / 60);
                                    } else {
                                        $net_worth = $last_net_worth;
                                    }

                                    $selected_player->kills = $player->kills;
                                    $selected_player->death = $player->deaths;
                                    $selected_player->assists = $player->assists;
                                    $selected_player->last_hits = $player->last_hits;
                                    $selected_player->denies = $player->denies;
                                    $selected_player->gold = $player->gold;
                                    $selected_player->level = $player->level;
                                    $selected_player->gold_per_min = $player->gold_per_min;
                                    $selected_player->xp_per_min = $player->xp_per_min;
                                    $selected_player->net_worth = $net_worth;
                                    $selected_player->save();

                                    $selected_player->items()->detach();
                                    for ($item_idx = 0; $item_idx < 6; $item_idx++) {
                                        if ($player->{'item_'.$item_idx} != 0) {
                                            $selected_player->items()->attach($player->{'item_'.$item_idx}, [
                                                'item_order' => $item_idx + 1,
                                                'created_at' => Carbon::now(),
                                                'updated_at' => Carbon::now()
                                            ]);
                                        }
                                    }

                                    if ($timelapse > 0) {
                                        $last_gold = $selected_player->golds()->where('duration', $old_duration)->first();
                                        if ($last_gold) {
                                            $last_gold = $last_gold->gold;
                                        } else {
                                            $last_gold = 625;
                                        }
                                        $selected_player_gold = new Dota2LiveMatchPlayerGold([
                                            'gold_per_min' => $player->gold_per_min,
                                            'gold' => $last_gold + ($timelapse * $player->gold_per_min / 60),
                                            'net_worth' => $net_worth,
                                            'duration' => $duration
                                        ]);
                                        $selected_player->golds()->save($selected_player_gold);
                                        if ($is_radiant_player) {
                                            array_push($radiant_golds, (object) [
                                                'id' => $selected_player->id,
                                                'net_worth' => $net_worth
                                            ]);
                                        } else {
                                            array_push($dire_golds, (object) [
                                                'id' => $selected_player->id,
                                                'net_worth' => $net_worth
                                            ]);
                                        }

                                        $last_xp = $selected_player->xps()->where('duration', $old_duration)->first();
                                        if ($last_xp) {
                                            $last_xp = $last_xp->xp;
                                        } else {
                                            $last_xp = 0;
                                        }
                                        $selected_player_xp = new Dota2LiveMatchPlayerXP([
                                            'xp_per_min' => $player->xp_per_min,
                                            'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60),
                                            'duration' => $duration
                                        ]);
                                        $selected_player->xps()->save($selected_player_xp);
                                        if ($is_radiant_player) {
                                            array_push($radiant_xps, (object) [
                                                'id' => $selected_player->id,
                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                            ]);
                                        } else {
                                            array_push($dire_xps, (object) [
                                                'id' => $selected_player->id,
                                                'xp' => $last_xp + ($timelapse * $player->xp_per_min / 60)
                                            ]);
                                        }
                                    }

                                    if (isset($player->ability_upgrades)) {
                                        $ability_order = $selected_player->abilities()->count() + 1;
                                        for ($ability_idx = $ability_order - 1; $ability_idx < count($player->ability_upgrades); $ability_idx++) { 
                                            $selected_player->abilities()->attach($player->ability_upgrades[$ability_idx]->ability, [
                                                'ability_order' => $ability_order,
                                                'created_at' => Carbon::now(),
                                                'updated_at' => Carbon::now()
                                            ]);

                                            $abilities_detail = null;
                                            if (isset($dota2_abilities[$player->ability_upgrades[$ability_idx]->ability])) {
                                                $abilities_detail = $dota2_abilities[$player->ability_upgrades[$ability_idx]->ability]->first();
                                            }
                                            if ($abilities_detail) {
                                                if ($is_radiant_player) {
                                                    array_push($radiant_abilities, (object) [
                                                        'id' => $selected_player->id,
                                                        'name' => $abilities_detail->name,
                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                        'ability_order' => $ability_order
                                                    ]);
                                                } else {
                                                    array_push($dire_abilities, (object) [
                                                        'id' => $selected_player->id,
                                                        'name' => $abilities_detail->name,
                                                        'picture_file_name' => $abilities_detail->picture_file_name,
                                                        'ability_order' => $ability_order
                                                    ]);
                                                }
                                            }

                                            $ability_order++;
                                        }
                                    }
                                }
                            }

                            $match = $dota2_live_match->match()->select('id', 'challonges_match_id', 'tournaments_id')->first();
                            if ($match) {
                                $radiant_score = $radiant->series_wins + ($radiant->matches_result == 3 ? 1 : 0);
                                $dire_score = $dire->series_wins + ($dire->matches_result == 3 ? 1 : 0);

                                if ($dota2_live_match->series_type == 0) {
                                    $reach_score_to_win = 1;
                                } else if ($dota2_live_match->series_type == 1) {
                                    $reach_score_to_win = 2;
                                } else if ($dota2_live_match->series_type == 2) {
                                    $reach_score_to_win = 3;
                                }

                                $radiant_matches_result = null;
                                $dire_matches_result = null;
                                if ($radiant_score == $reach_score_to_win) {
                                    $radiant_matches_result = 3;
                                    $dire_matches_result = 1;
                                } else if ($dire_score == $reach_score_to_win) {
                                    $radiant_matches_result = 1;
                                    $dire_matches_result = 3;
                                }

                                $radiant_challonges = $match->participants()->select('tournaments_registrations.id AS id', 'tournaments_registrations.challonges_participants_id AS challonges_participants_id', 'matches_participants.side AS side')->find($radiant->tournaments_registrations_id);
                                $dire_challonges = $match->participants()->select('tournaments_registrations.id AS id', 'tournaments_registrations.challonges_participants_id AS challonges_participants_id', 'matches_participants.side AS side')->find($dire->tournaments_registrations_id);

                                $success = false;
                                if ($match->challonges_match_id) {
                                    $tournament = $match->tournament()->select('id', 'type', 'challonges_id')->first();
                                    if ($tournament) {
                                        if ($tournament->challonges_id) {
                                            if ($radiant_challonges && $dire_challonges) {
                                                $winner_id = null;
                                                if ($radiant_matches_result == 3) {
                                                    $winner_id = $radiant_challonges->challonges_participants_id;
                                                } else if ($dire_matches_result == 3) {
                                                    $winner_id = $dire_challonges->challonges_participants_id;
                                                }

                                                if ($radiant_challonges->side == 1) {
                                                    $scores_csv = $radiant_score.'-'.$dire_score;
                                                } else if ($dire_challonges->side == 1) {
                                                    $scores_csv = $dire_score.'-'.$radiant_score;
                                                }

                                                $success = GuzzleHelper::updateTournamentMatchScore($tournament, $match, $scores_csv, $winner_id);
                                            }
                                        }
                                    }
                                } else {
                                    $success = true;
                                }

                                if ($success && $radiant_challonges && $dire_challonges) {
                                    $match->participants()->updateExistingPivot($radiant_challonges->id, [
                                        'score' => $radiant_score,
                                        'matches_result' => $radiant_matches_result
                                    ]);
                                    $match->participants()->updateExistingPivot($dire_challonges->id, [
                                        'score' => $dire_score,
                                        'matches_result' => $dire_matches_result
                                    ]);

                                    if ($radiant_matches_result && $dire_matches_result) {
                                        $match->load([
                                            'parents' => function($parents) {
                                                $parents->select('matches.id', 'matches_qualifications_details.from_child_matches_result AS from_child_matches_result', 'matches_qualifications_details.side AS side');
                                            }
                                        ]);

                                        foreach ($match->parents as $parent) {
                                            if ($radiant_matches_result == $parent->from_child_matches_result) {
                                                $parent->participants()->attach($radiant_challonges->id, [
                                                    'side' => $parent->side,
                                                    'created_at' => Carbon::now(),
                                                    'updated_at' => Carbon::now()
                                                ]);
                                            } else if ($dire_matches_result == $parent->from_child_matches_result) {
                                                $parent->participants()->attach($dire_challonges->id, [
                                                    'side' => $parent->side,
                                                    'created_at' => Carbon::now(),
                                                    'updated_at' => Carbon::now()
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        DB::commit();

                        event(new Dota2LiveMatchUpdated($dota2_live_match, $radiant_golds, $radiant_xps, $dire_golds, $dire_xps));
                        event(new Dota2LiveMatchPlayersItemsUpdated($dota2_live_match));
                        event(new Dota2LiveMatchRadiantPlayersUpdated($dota2_live_match, $radiant_abilities));
                        event(new Dota2LiveMatchDirePlayersUpdated($dota2_live_match, $dire_abilities));
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                }
            }
        }
    }
}
