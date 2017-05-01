<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['name', 'picture_file_name', 'join_password'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function details()
    {
        return $this->belongsToMany('App\Member', 'teams_details', 'teams_id', 'members_id');
    }

    public function invitation_list()
    {
        return $this->hasMany('App\NotificationTeamInvitation', 'teams_id', 'id');
    }

    public static function checkNameExists($name, $team_id = null)
    {
        if ($team_id) {
            return self::where('name', $name)->where('id', '<>', $team_id)->exists();
        } else {
            return self::where('name', $name)->exists();
        }
    }
}
