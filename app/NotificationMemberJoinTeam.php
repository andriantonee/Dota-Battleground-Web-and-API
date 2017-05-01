<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationMemberJoinTeam extends Model
{
    protected $table = 'notifications_members_join_teams';

    protected $primaryKey = 'notifications_id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['teams_id', 'members_id'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = false;

    public function member()
    {
        return $this->belongsTo('App\Member', 'members_id', 'id');
    }

    public function team()
    {
        return $this->belongsTo('App\Team', 'teams_id', 'id');
    }
}
