<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2Hero extends Model
{
    protected $table = 'dota2_heroes';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['id', 'name_id', 'name', 'picture_file_name'];
    protected $hidden = ['pivot'];

    public $incrementing = false;

    public $timestamps = true;

    public function abilities()
    {
    	return $this->belongsToMany('App\Dota2Ability', 'dota2_heroes_abilities', 'dota2_heroes_id', 'dota2_abilities_id');
    }
}
