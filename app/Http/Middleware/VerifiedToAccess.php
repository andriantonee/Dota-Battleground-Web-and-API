<?php

namespace App\Http\Middleware;

use Closure;

class VerifiedToAccess
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
        if ($guard == 'participant') {
            $member = $request->user();
            $has_verified_identifications = $member->identifications()
                ->where('verified', 1)
                ->exists();

            if (!$has_verified_identifications) {
                if ($request->segment(1) == 'api') {
                    return response()->json(['code' => 404, 'message' => ['Member doesn\'t has verified Identity Card.']]);
                } else {
                    abort(404);
                }
            }
        } else if ($guard == 'organizer') {
            if ($request->user()->verified != 1) {
                if ($request->segment(1) == 'api') {
                    return response()->json(['code' => 404, 'message' => ['Member is not verified.']]);
                } else {
                    abort(404);
                }
            }
        }

        return $next($request);
    }
}
