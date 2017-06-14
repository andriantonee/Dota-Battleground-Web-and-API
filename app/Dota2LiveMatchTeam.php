<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2LiveMatchTeam extends Model
{
    protected $table = 'dota2_live_matches_teams';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['dota2_teams_id', 'dota2_teams_name', 'dota2_teams_logo', 'series_wins', 'score', 'tower_state', 'barracks_state', 'side'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function tournament_registration()
    {
        return $this->belongsTo('App\TournamentRegistration', 'tournaments_registrations_id', 'id');
    }

    public function dota2_live_match()
    {
        return $this->belongsTo('App\Dota2LiveMatch', 'dota2_live_matches_id', 'id');
    }

    public function heroes_pick()
    {
        return $this->belongsToMany('App\Dota2Hero', 'dota2_live_matches_teams_picks', 'dota2_live_matches_teams_id', 'dota2_heroes_id');
    }

    public function heroes_ban()
    {
        return $this->belongsToMany('App\Dota2Hero', 'dota2_live_matches_teams_bans', 'dota2_live_matches_teams_id', 'dota2_heroes_id');
    }

    public function dota2_live_match_players()
    {
        return $this->hasMany('App\Dota2LiveMatchPlayer', 'dota2_live_matches_teams_id', 'id');
    }
}
