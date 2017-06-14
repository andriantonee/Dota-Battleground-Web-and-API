<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2Ability extends Model
{
    protected $table = 'dota2_abilities';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['id', 'name_id', 'name', 'picture_file_name'];
    protected $hidden = ['pivot'];

    public $incrementing = false;

    public $timestamps = true;

    public function heroes()
    {
    	return $this->belongsToMany('App\Dota2Hero', 'dota2_heroes_abilities', 'dota2_abilities_id', 'dota2_heroes_id');
    }
}
