<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2LiveMatchPlayerGold extends Model
{
    protected $table = 'dota2_live_matches_players_golds';

    protected $primaryKey = null;
    protected $keyType = null;

    protected $dates = [];
    protected $fillable = ['gold_per_min', 'gold', 'net_worth', 'duration'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = true;

    public function dota2_live_match_player()
    {
        return $this->belongsTo('App\Dota2LiveMatchPlayer', 'dota2_live_matches_players_id', 'id');
    }
}
