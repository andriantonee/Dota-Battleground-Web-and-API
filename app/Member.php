<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Member extends Model
{
    use HasApiTokens;

    protected $table = 'members';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['email', 'member_type', 'password', 'name'];
    protected $hidden = ['password'];

    public $incrementing = true;

    public $timestamps = true;

    /**
     * modifier to validate user using passport authentication.
     *
     * @return mixed
     */
    public function findForPassport($id)
    {
        return self::where('id', $id)->first();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function identifications()
    {
        return $this->hasMany('App\Identification', 'members_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany('App\Team', 'teams_details', 'members_id', 'teams_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification', 'members_id', 'id');
    }

    public function tournaments_registrations()
    {
        return $this->belongsToMany('App\TournamentRegistration', 'tournaments_registrations_details', 'members_id', 'tournaments_registrations_id');
    }

    public static function checkEmailExists($email, $member_type, $member_id = null)
    {
        if ($member_id) {
            return self::where('email', $email)->where('id', '<>', $member_id)->where('member_type', $member_type)->exists();
        } else {
            return self::where('email', $email)->where('member_type', $member_type)->exists();
        }
    }

    public static function getMemberIDByEmail($email, $member_type)
    {
        return self::where('email', $email)->where('member_type', $member_type)->value('id');
    }
}
