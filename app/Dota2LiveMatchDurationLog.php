<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2LiveMatchDurationLog extends Model
{
    protected $table = 'dota2_live_matches_durations_logs';

    protected $primaryKey = null;
    protected $keyType = null;

    protected $dates = [];
    protected $fillable = ['duration'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = true;

    public function dota2_live_match()
    {
        return $this->belongsTo('App\Dota2LiveMatch', 'dota2_live_matches_id', 'id');
    }
}
