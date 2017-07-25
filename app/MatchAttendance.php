<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchAttendance extends Model
{
    protected $table = 'matches_attendances';

    protected $primaryKey = null;
    protected $keyType = null;

    protected $dates = [];
    protected $fillable = ['qr_identifier'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = true;

    public function match()
    {
        return $this->belongsTo('App\Match', 'matches_id', 'id');
    }
}
