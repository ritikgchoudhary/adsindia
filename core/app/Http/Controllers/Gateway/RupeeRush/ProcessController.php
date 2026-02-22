<?php

namespace App\Http\Controllers\Gateway\RupeeRush;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Lib\RupeeRushGateway;

class ProcessController extends Controller
{
    /**
     * RupeeRush server-to-server callback (notifyUrl).
     */
    public function ipn(Request $request)
    {
        $allowedIps = ['8.222.246.219', '47.245.81.104', '47.236.92.61'];
        if (!in_array($request->ip(), $allowedIps)) {
            \Log::warning('Unauthorized RupeeRush IP: ' . $request->ip());
            return response('unauthorized', 403);
        }

        $payload = $request->all();

        if (!RupeeRushGateway::verifyCallback($payload)) {
            return response('sign error', 400);
        }

        // Standard resultCode '0000' is success in request, but for callback, check documentation
        // Usually, these gateways send '0000' or 'SUCCESS' in a status field.
        // If not specified, we assume presence of certain fields or specific status.
        // I will check the resultCode in the payload.
        if (($payload['resultCode'] ?? null) !== '0000') {
             return response('not success', 200);
        }

        $mchOrderNo = (string) ($payload['outTradeNo'] ?? '');
        if ($mchOrderNo === '') {
            return response('outTradeNo missing', 200);
        }

        // Mark payment session success (based on cache key pattern used in this project)
        // Check SimplyPay's pattern: 'simplypay_payment_' . $mchOrderNo
        // We'll use 'adspay_payment_' . $mchOrderNo
        $key = 'rupeerush_payment_' . $mchOrderNo;
        $session = cache()->get($key);
        if (is_array($session)) {
            $session['status'] = 'success';
            $session['paid_at'] = now()->format('Y-m-d H:i:s');
            $session['gateway'] = [
                'payOrderNo' => $payload['payOrderNo'] ?? null,
                'outTradeNo' => $payload['outTradeNo'] ?? null,
                'totalAmount' => $payload['totalAmount'] ?? null,
            ];
            cache()->put($key, $session, now()->addHours(2));
        }

        return response('SUCCESS', 200);
    }
}
