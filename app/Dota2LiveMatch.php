<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2LiveMatch extends Model
{
    protected $table = 'dota2_live_matches';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['id', 'leagues_id', 'series_type', 'spectators', 'duration', 'roshan_respawn_timer'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = true;

    public function match()
    {
        return $this->belongsTo('App\Match', 'matches_id', 'id');
    }

    public function dota2_live_match_teams()
    {
        return $this->hasMany('App\Dota2LiveMatchTeam', 'dota2_live_matches_id', 'id');
    }

    public function durations()
    {
        return $this->hasMany('App\Dota2LiveMatchDurationLog', 'dota2_live_matches_id', 'id');
    }
}
