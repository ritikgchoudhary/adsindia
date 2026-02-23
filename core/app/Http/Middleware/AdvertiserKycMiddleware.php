<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use Closure;
use Illuminate\Http\Request;

class AdvertiserKycMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $advertiser = auth()->guard('advertiser')->user();
        if ($request->is('api/*') && ($advertiser->kv == Status::KYC_UNVERIFIED || $advertiser->kv == Status::KYC_PENDING)) {
            $notify[] = 'You are unable to withdraw due to KYC verification';
            return response()->json([
                'remark' => 'kyc_verification',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
        if ($advertiser->kv == Status::KYC_UNVERIFIED) {
            $notify[] = ['error', 'You are not KYC verified. For being KYC verified, please provide these information'];
            return to_route('advertiser.kyc.form')->withNotify($notify);
        }
        if ($advertiser->kv == Status::KYC_PENDING) {
            $notify[] = ['warning', 'Your documents for KYC verification is under review. Please wait for admin approval'];
            return to_route('advertiser.home')->withNotify($notify);
        }
        return $next($request);
    }
}
