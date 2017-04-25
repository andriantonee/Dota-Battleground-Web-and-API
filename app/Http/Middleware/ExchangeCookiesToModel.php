<?php

namespace App\Http\Middleware;

use App\Helpers\GuzzleHelper;
use Closure;

class ExchangeCookiesToModel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'participant', $member_type = 1)
    {
        $model = null;
        $request->merge([$guard.'_model' => $model]);
        if ($cookie = $request->cookie($guard.'_token')) {
            $model = GuzzleHelper::requestUserModel($cookie);
            if ($model) {
                if ($model->member_type == $member_type) {
                    $request->merge([$guard.'_model' => $model]);
                }
            }
        }

        view()->share('user', $model);

        return $next($request);
    }
}
