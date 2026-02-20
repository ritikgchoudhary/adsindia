<?php

namespace App\Http\Controllers\Gateway\WatchPay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    /**
     * WatchPay server-to-server callback (notify_url).
     *
     * We mark cached payment sessions as paid so API flows (registration/packages/ad-plans)
     * can confirm and complete their actions.
     */
    public function ipn(Request $request)
    {
        $payload = $request->all();

        // Verify sign
        if (!\App\Lib\WatchPayGateway::verifyCallback($payload)) {
            return response('sign error', 400);
        }

        $tradeResult = (string) ($payload['tradeResult'] ?? '');
        if ($tradeResult !== '1') {
            // Payment failed; still respond 200 to stop retries
            return response('fail', 200);
        }

        // WatchPay sends merchant order no as mchOrderNo
        $mchOrderNo = (string) ($payload['mchOrderNo'] ?? $payload['mch_order_no'] ?? '');
        if ($mchOrderNo === '') {
            return response('success', 200);
        }

        // Mark payment session success (if exists)
        $key = 'watchpay_payment_' . $mchOrderNo;
        $session = cache()->get($key);
        if (is_array($session)) {
            $session['status'] = 'success';
            $session['paid_at'] = now()->format('Y-m-d H:i:s');
            $session['gateway'] = [
                'orderNo' => $payload['orderNo'] ?? null,
                'mchOrderNo' => $payload['mchOrderNo'] ?? null,
                'amount' => $payload['amount'] ?? null,
            ];
            cache()->put($key, $session, now()->addHours(2));
        }

        return response('success', 200);
    }
}

