<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentRegistrationConfirmation extends Model
{
    protected $table = 'tournaments_registrations_confirmations';

    protected $primaryKey = 'tournaments_registrations_id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['name', 'banks_id', 'confirmation_file_name'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = true;

    public function registration()
    {
        return $this->belongsTo('App\TournamentRegistration', 'tournaments_registrations_id', 'id');
    }

    public function approval()
    {
        return $this->hasOne('App\TournamentRegistrationConfirmationApproval', 'tournaments_registrations_confirmations_id', 'tournaments_registrations_id');
    }
}
