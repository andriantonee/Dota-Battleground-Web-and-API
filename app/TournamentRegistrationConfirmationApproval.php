<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TournamentRegistrationConfirmationApproval extends Model
{
    protected $table = 'tournaments_registrations_confirmations_approvals';

    protected $primaryKey = 'tournaments_registrations_confirmations_id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['status'];
    protected $hidden = [];

    public $incrementing = false;

    public $timestamps = true;

    public function confirmation()
    {
        return $this->belongsTo('App\TouranmentRegistrationConfirmation', 'tournaments_registrations_id', 'tournaments_registrations_confirmations_id');
    }
}
