<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfAuthorized
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
            if ($guard == 'organizer') {
                return redirect('/organizer/dashboard');
            } else if ($guard == 'admin') {
                return redirect('/admin');
            }
        }

        return $next($request);
    }
}
