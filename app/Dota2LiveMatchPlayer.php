<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2LiveMatchPlayer extends Model
{
    protected $table = 'dota2_live_matches_players';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['steam32_id', 'name', 'kills', 'death', 'assists', 'last_hits', 'denies', 'gold', 'level', 'gold_per_min', 'xp_per_min', 'respawn_timer', 'position_x', 'position_y', 'net_worth', 'player_order'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function member()
    {
        return $this->belongsTo('App\Member', 'members_id', 'id');
    }

    public function dota2_live_match_team()
    {
        return $this->belongsTo('App\Dota2LiveMatchTeam', 'dota2_live_matches_teams_id', 'id');
    }

    public function hero()
    {
        return $this->belongsTo('App\Dota2Hero', 'dota2_heroes_id', 'id');
    }

    public function abilities()
    {
        return $this->belongsToMany('App\Dota2Ability', 'dota2_live_matches_players_abilities', 'dota2_live_matches_players_id', 'dota2_abilities_id');
    }

    public function items()
    {
        return $this->belongsToMany('App\Dota2Item', 'dota2_live_matches_players_items', 'dota2_live_matches_players_id', 'dota2_items_id');
    }

    public function golds()
    {
        return $this->hasMany('App\Dota2LiveMatchPlayerGold', 'dota2_live_matches_players_id', 'id');
    }

    public function xps()
    {
        return $this->hasMany('App\Dota2LiveMatchPlayerXP', 'dota2_live_matches_players_id', 'id');
    }
}
