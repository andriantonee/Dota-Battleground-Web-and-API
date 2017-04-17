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

    public static function checkEmailExists($email, $member_type)
    {
        return self::where('email', $email)->where('member_type', $member_type)->exists();
    }

    public static function getMemberIDByEmail($email, $member_type)
    {
        return self::where('email', $email)->where('member_type', $member_type)->value('id');
    }
}
