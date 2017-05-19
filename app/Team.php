<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'id';
    protected $keyType = 'integer';

    protected $dates = [];
    protected $fillable = ['name', 'picture_file_name', 'join_password'];
    protected $hidden = [];

    public $incrementing = true;

    public $timestamps = true;

    public function details()
    {
        return $this->belongsToMany('App\Member', 'teams_details', 'teams_id', 'members_id');
    }

    public function invitation_list()
    {
        return $this->hasMany('App\NotificationTeamInvitation', 'teams_id', 'id');
    }

    public function tournaments()
    {
        return $this->hasMany('App\TournamentRegistration', 'teams_id', 'id');
    }

    public static function checkNameExists($name, $team_id = null)
    {
        if ($team_id) {
            return self::where('name', $name)->where('id', '<>', $team_id)->exists();
        } else {
            return self::where('name', $name)->exists();
        }
    }

    public static function getLists($member_id, $search_keyword = null)
    {
        $teams = self::select('id', 'name', 'picture_file_name', 'join_password')
            ->withCount('details')
            ->with([
                'details' => function($details) use($member_id) {
                    $details->where('teams_details.members_id', $member_id);
                },
                'invitation_list' => function($invitation_list) use($member_id) {
                    $invitation_list->where('notifications_teams_invitations.invitation_status', 1)
                        ->whereHas('parent', function($parent) use($member_id) {
                            $parent->where('notifications.members_id', $member_id);
                        });
                }
            ])
            ->orderBy('teams.created_at', 'DESC');
        if ($search_keyword) {
            $teams = $teams->where('name', 'LIKE', '%'.$search_keyword.'%');
        }

        return $teams->get();
    }

    public static function getListsForTournaments($member_id, $tournament_id)
    {
        $teams = self::select('id', 'name', 'picture_file_name')
            ->withCount('details')
            ->whereHas('details', function($details) use($member_id) {
                $details->where('members_id', $member_id)
                    ->where('members_privilege', 2);
            })
            ->whereDoesntHave('tournaments', function($tournaments) use($tournament_id) {
                $tournaments->where('tournaments_id', $tournament_id);
            })
            ->orderBy('name', 'ASC')
            ->get();

        return $teams;
    }
}
