<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Identification extends Model
{
    protected $table = 'identifications';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['identification_file_name'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;
}
