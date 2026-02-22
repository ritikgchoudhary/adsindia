<?php

namespace App\Http\Controllers\Gateway\SimplyPay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Lib\SimplyPayGateway;

class ProcessController extends Controller
{
    /**
     * SimplyPay server-to-server callback (notifyUrl).
     */
    public function ipn(Request $request)
    {
        \Log::info('SimplyPay Callback Received', ['ip' => $request->ip(), 'payload' => $request->all()]);

        // Validate IP if provided by user
        $allowedIps = ['15.207.74.156', '159.89.168.162'];
        if (!in_array($request->ip(), $allowedIps) && env('APP_ENV') === 'production') {
             \Log::warning('SimplyPay Callback Unauthorized IP', ['ip' => $request->ip()]);
             // return response('Unauthorized IP', 403); // Uncomment for strict security
        }

        $payload = $request->all();

        // If payload is wrapped in 'data', unwrap it (though verifyCallback should handle it if passed correctly)
        // Some gateways send the whole response object including code and msg.
        if (isset($payload['data']) && is_array($payload['data'])) {
            $data = $payload['data'];
            // Re-sign verification often expects the inner data object
            if (SimplyPayGateway::verifyCallback($data)) {
                $payload = $data;
            } else {
                 return response('sign error 1', 400);
            }
        } else {
             if (!SimplyPayGateway::verifyCallback($payload)) {
                return response('sign error 2', 400);
            }
        }

        // status: pending: 0,1,-4 | success: 2,3 | failed: -1,-2 | refunded: -3
        $orderStatus = (int) ($payload['orderStatus'] ?? -1);
        if ($orderStatus !== 2 && $orderStatus !== 3) {
            // Not a success status; still respond success to gateway to acknowledge receipt
            return response('ok', 200);
        }

        $mchOrderNo = (string) ($payload['merOrderNo'] ?? '');
        if ($mchOrderNo === '') {
            return response('merOrderNo missing', 200);
        }

        // Mark payment session success (if exists)
        $key = 'simplypay_payment_' . $mchOrderNo;
        $session = cache()->get($key);
        if (is_array($session)) {
            $session['status'] = 'success';
            $session['paid_at'] = now()->format('Y-m-d H:i:s');
            $session['gateway'] = [
                'orderNo' => $payload['orderNo'] ?? null,
                'merOrderNo' => $payload['merOrderNo'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'currency' => $payload['currency'] ?? null,
            ];
            cache()->put($key, $session, now()->addHours(2));
        }

        // 🟢 Fulfill Deposit in database (if exists)
        $deposit = \App\Models\Deposit::where('trx', $mchOrderNo)->where('status', \App\Constants\Status::PAYMENT_INITIATE)->first();
        if ($deposit) {
            \App\Http\Controllers\Gateway\PaymentController::userDataUpdate($deposit);
        }

        return response('success', 200);
    }
}
