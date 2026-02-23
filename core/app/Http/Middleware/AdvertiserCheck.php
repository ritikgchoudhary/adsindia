<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AdvertiserCheck
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
        if (Auth::guard('advertiser')->check()) {
            $advertiser = auth()->guard('advertiser')->user();
            if ($advertiser->status  && $advertiser->ev  && $advertiser->sv  && $advertiser->tv) {
                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'You need to verify your account first. Please logout and re-login';
                    return response()->json([
                        'remark' => 'unverified',
                        'status' => 'error',
                        'message' => ['error' => $notify],
                        'data' => [
                            'advertiser' => $advertiser
                        ],
                    ]);
                } else {
                    return to_route('advertiser.authorization');
                }
            }
        }
        abort(403);
    }
}
