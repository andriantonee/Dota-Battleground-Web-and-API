<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    protected $table = 'matches';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['challonges_match_id', 'round'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function tournament()
    {
        return $this->belongsTo('App\Tournament', 'tournaments_id', 'id');
    }

    public function participants()
    {
        return $this->belongsToMany('App\TournamentRegistration', 'matches_participants', 'matches_id', 'tournaments_registrations_id');
    }

    public function childs()
    {
        return $this->belongsToMany('App\Match', 'matches_qualifications_details', 'parent_matches_id', 'child_matches_id');
    }
}
