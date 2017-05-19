<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentApproval extends Model
{
    protected $table = 'tournaments_approvals';

    protected $primaryKey = 'tournaments_id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['accepted'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = true;

    public function member()
    {
        return $this->belongsTo('App\Member', 'members_id', 'id');
    }
}
