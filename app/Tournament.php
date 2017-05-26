<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $table = 'tournaments';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['name', 'description', 'logo_file_name', 'type', 'leagues_id', 'address', 'max_participant', 'rules', 'prize_1st', 'prize_2nd', 'prize_3rd', 'prize_other', 'entry_fee', 'registration_closed', 'need_identifications', 'start_date', 'end_date'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function city()
    {
        return $this->belongsTo('App\City', 'cities_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Member', 'members_id', 'id');
    }

    public function approval()
    {
        return $this->hasOne('App\TournamentApproval', 'tournaments_id', 'id');
    }

    public function registrations()
    {
        return $this->hasMany('App\TournamentRegistration', 'tournaments_id', 'id');
    }

    public function matches()
    {
        return $this->hasMany('App\Match', 'tournaments_id', 'id');
    }

    public static function checkLeagueIDExists($leagues_id, $tournament_id = null)
    {
        if ($tournament_id) {
            return self::where('leagues_id', $leagues_id)->where('id', '<>', $tournament_id)->exists();
        } else {
            return self::where('leagues_id', $leagues_id)->exists();
        }
    }
}
