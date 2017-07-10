<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['read_status'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function member_join_team()
    {
        return $this->hasOne('App\NotificationMemberJoinTeam', 'notifications_id', 'id');
    }

    public function team_invitation()
    {
        return $this->hasOne('App\NotificationTeamInvitation', 'notifications_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo('App\Member', 'members_id', 'id');
    }
}
