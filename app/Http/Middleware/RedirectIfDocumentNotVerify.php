<?php

namespace App\Http\Middleware;

use App\Member;
use Closure;

class RedirectIfDocumentNotVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'organizer')
    {
        if ($request->input($guard.'_model')) {
            if ($guard == 'participant') {
                $member = Member::find($request->input($guard.'_model')->id);
                $has_verified_identifications = $member->identifications()
                    ->where('verified', 1)
                    ->exists();

                if (!$has_verified_identifications) {
                    return redirect('/profile');
                }
            } else if ($guard == 'organizer') {
                if ($request->input($guard.'_model')->verified != 1) {
                    if ($request->path() != 'organizer/document') {
                        return redirect('/organizer/document');
                    }
                } else {
                    if ($request->path() == 'organizer/document') {
                        return redirect('/organizer/dashboard');
                    }
                }
            }
        }

        return $next($request);
    }
}
