<?php

namespace App\Http\Middleware;

use App\Member;
use App\Notification as NotificationModel;
use Closure;

class Notification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $notification_id = (int) $request->input('notification_id');
        if ($notification_id) {
            $notification = NotificationModel::find($notification_id);
            $notification->read_status = 1;
            $notification->save();
        }

        if ($request->input('participant_model')) {
            $member = Member::find($request->input('participant_model')->id);
            $unread_notifications = $member->notifications()->where('read_status', 0)->count('*');
            $notifications = $member->notifications()
                ->with([
                    'member_join_team' => function($member_join_team) {
                        $member_join_team->with('team', 'member');
                    },
                    'team_invitation' => function($team_invitation) {
                        $team_invitation->with('team');
                    }
                ])
                ->orderBy('created_at', 'DESC')
                ->get();

            view()->share('notifications', $notifications);
            view()->share('unread_notifications', $unread_notifications);
        }

        return $next($request);
    }
}
