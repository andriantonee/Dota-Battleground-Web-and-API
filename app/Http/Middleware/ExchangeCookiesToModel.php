<?php

namespace App\Http\Middleware;

use App\Helpers\GuzzleHelper;
use App\Member;
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
                    if ($model->member_type == 1) {
                        $member = Member::find($model->id);
                        $has_verified_identifications = $member->identifications()
                            ->where('verified', 1)
                            ->exists();

                        view()->share('has_verified_identifications', $has_verified_identifications);
                    }
                }
            }
        }

        view()->share($guard, $model);

        return $next($request);
    }
}
