<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationTeamInvitation extends Model
{
    protected $table = 'notifications_teams_invitations';

    protected $primaryKey = 'notifications_id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['teams_id', 'invitation_status'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = false;

    public function team()
    {
        return $this->belongsTo('App\Team', 'teams_id', 'id');
    }

    public function parent()
    {
        return $this->hasOne('App\Notification', 'id', 'notifications_id');
    }
}
