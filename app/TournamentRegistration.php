<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentRegistration extends Model
{
    protected $table = 'tournaments_registrations';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = [];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function tournament()
    {
        return $this->belongsTo('App\Tournament', 'tournaments_id', 'id');
    }

    public function team()
    {
        return $this->belongsTo('App\Team', 'teams_id', 'id');
    }

    public function members()
    {
        return $this->belongsToMany('App\Member', 'tournaments_registrations_details', 'tournaments_registrations_id', 'members_id');
    }

    public function confirmation()
    {
        return $this->hasOne('App\TournamentRegistrationConfirmation', 'tournaments_registrations_id', 'id');
    }

    public static function checkTournamentRegisterExists($team_id, $tournament_id)
    {
        return self::where('tournaments_id', $tournament_id)->where('teams_id', $team_id)->exists();
    }
}
