<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GatewayTestController extends Controller
{
    /**
     * Initiate a gateway test payment from Master Admin.
     * Supports watchpay, simplypay, rupeerush.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:100000',
            'gateway' => 'required|in:watchpay,simplypay,rupeerush',
        ]);

        $gateway = $request->gateway;
        $amount = (float) $request->amount;
        $trx = 'TEST' . getTrx();

        $cachePrefix = $gateway . '_payment_';
        cache()->put($cachePrefix . $trx, [
            'type' => 'gateway_test',
            'actor' => 'admin',
            'admin_id' => optional($request->user())->id,
            'amount' => $amount,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/master_admin/settings?test_trx=' . urlencode($trx) . '&gateway=' . urlencode($gateway);
        $notifyUrl = $base . '/ipn/' . $gateway;

        try {
            if ($gateway === 'simplypay') {
                $res = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => $amount,
                    'goodsName' => 'Gateway Test: SimplyPay',
                    'returnUrl' => $pageUrl,
                    'notifyUrl' => $notifyUrl,
                    'name' => 'Admin Tester',
                    'email' => 'admin@test.com',
                    'mobile' => '9876543210'
                ]);
            } elseif ($gateway === 'rupeerush') {
                $res = \App\Lib\RupeeRushGateway::createPayment([
                    'outTradeNo' => $trx,
                    'totalAmount' => $amount,
                    'notifyUrl' => $notifyUrl,
                    'payViewUrl' => $pageUrl,
                    'payName' => 'Admin Tester',
                    'payEmail' => 'admin@test.com',
                    'payPhone' => '9876543210',
                ]);
            } else {
                $res = \App\Lib\WatchPayGateway::createWebPayment(
                    $trx,
                    $amount,
                    'Gateway Test: WatchPay',
                    $pageUrl,
                    $notifyUrl
                );
            }
        } catch (\Throwable $e) {
            \Log::error('Gateway test payment init failed', [
                'gateway' => $gateway,
                'admin_id' => optional($request->user())->id,
                'amount' => $amount,
                'trx' => $trx,
                'error' => $e->getMessage(),
            ]);
            return responseError('payment_gateway_error', [$e->getMessage() ?: 'Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $res['pay_link'] ?? '',
            'trx' => $trx,
            'amount' => $amount,
            'gateway' => $gateway,
            'gateway_name' => ucfirst($gateway),
        ]);
    }

    /**
     * Check status of test payment session (updated by IPN).
     */
    public function status(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
            'gateway' => 'required|in:watchpay,simplypay,rupeerush',
        ]);

        $trx = (string) $request->trx;
        $gateway = (string) $request->gateway;
        $cachePrefix = $gateway . '_payment_';

        $session = cache()->get($cachePrefix . $trx);
        if (!is_array($session) || ($session['type'] ?? '') !== 'gateway_test') {
            return responseError('payment_not_found', ['Payment session not found']);
        }

        return responseSuccess('payment_status', ['Payment status retrieved'], [
            'trx' => $trx,
            'gateway' => $gateway,
            'status' => (string) ($session['status'] ?? 'pending'),
            'paid_at' => $session['paid_at'] ?? null,
            'amount' => $session['amount'] ?? null,
        ]);
    }
}
