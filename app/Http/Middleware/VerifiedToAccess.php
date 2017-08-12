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
    public function handle($request, Closure $next)
    {
        if ($request->user()->verified != 1) {
            if ($request->segment(1) == 'api') {
                return response()->json(['code' => 404, 'message' => ['Member is not verified.']]);
            } else {
                abort(404);
            }
        }

        return $next($request);
    }
}
