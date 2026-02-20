<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WatchPayTestController extends Controller
{
    /**
     * Initiate a WatchPay test payment from Master Admin.
     * Returns payment_url (pay_link) and trx.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:100000',
        ]);

        $amount = (float) $request->amount;
        $trx = 'TEST' . getTrx();

        cache()->put('watchpay_payment_' . $trx, [
            'type' => 'gateway_test',
            'actor' => 'admin',
            'admin_id' => optional($request->user())->id,
            'amount' => $amount,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/master_admin/settings?watchpay_test_trx=' . urlencode($trx);
        $notifyUrl = $base . '/ipn/watchpay';

        try {
            $wp = \App\Lib\WatchPayGateway::createWebPayment(
                $trx,
                $amount,
                'WatchPay Gateway Test',
                $pageUrl,
                $notifyUrl
            );
        } catch (\Throwable $e) {
            \Log::error('WatchPay test payment init failed', [
                'admin_id' => optional($request->user())->id,
                'amount' => $amount,
                'trx' => $trx,
                'error' => $e->getMessage(),
            ]);
            return responseError('payment_gateway_error', [$e->getMessage() ?: 'Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $wp['pay_link'] ?? '',
            'trx' => $trx,
            'amount' => $amount,
            'gateway_name' => 'WatchPay',
        ]);
    }

    /**
     * Check status of test payment session (updated by WatchPay IPN).
     */
    public function status(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
        ]);

        $trx = (string) $request->trx;
        $session = cache()->get('watchpay_payment_' . $trx);
        if (!is_array($session) || ($session['type'] ?? '') !== 'gateway_test') {
            return responseError('payment_not_found', ['Payment session not found']);
        }

        return responseSuccess('payment_status', ['Payment status retrieved'], [
            'trx' => $trx,
            'status' => (string) ($session['status'] ?? 'pending'),
            'paid_at' => $session['paid_at'] ?? null,
            'amount' => $session['amount'] ?? null,
        ]);
    }
}

