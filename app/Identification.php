<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Identification extends Model
{
    protected $table = 'identifications';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['identification_file_name', 'verified'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function member()
    {
    	return $this->belongsTo('App\Member', 'members_id', 'id');
    }
}
