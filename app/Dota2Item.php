<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dota2Item extends Model
{
    protected $table = 'dota2_items';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['id', 'name_id', 'name', 'picture_file_name'];
    protected $hidden = ['pivot'];

    public $incrementing = false;

    public $timestamps = true;
}
