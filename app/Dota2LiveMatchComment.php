<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2LiveMatchComment extends Model
{
    protected $table = 'dota2_live_matches_comments';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['detail'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function dota2_live_match()
    {
        return $this->belongsTo('App\Dota2LiveMatch', 'dota2_live_matches_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo('App\Member', 'members_id', 'id');
    }
}
