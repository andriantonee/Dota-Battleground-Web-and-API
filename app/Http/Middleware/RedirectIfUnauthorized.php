<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfUnauthorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'participant')
    {
        if (!$request->input($guard.'_model')) {
            if ($guard == 'participant') {
                return redirect('/login');
            } else {
                return response()->json(['Unauthorize'], 401);
            }
        }

        return $next($request);
    }
}
