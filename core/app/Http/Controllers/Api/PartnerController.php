<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function getCurrentPlan()
    {
        $user = auth()->user();
        $general = gs();

        $currentPlanId = $user->partner_plan_id ?? null;

        return responseSuccess('current_partner_plan', ['Current partner plan retrieved successfully'], [
            'plan_id' => $currentPlanId,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function getPartnerPlans()
    {
        $general = gs();

        $plans = [
            [
                'id' => 1,
                'name' => 'Associate Partner',
                'price' => 1999,
                'referral_commission' => 1000,
                'downline_commission' => 0,
                'description' => 'Perfect for beginners to start earning referral income.',
            ],
            [
                'id' => 2,
                'name' => 'Executive Partner',
                'price' => 3999,
                'referral_commission' => 1500,
                'downline_commission' => 10,
                'description' => 'Unlock downline income and boost your monthly earnings.',
            ],
            [
                'id' => 3,
                'name' => 'Master Partner',
                'price' => 5999,
                'referral_commission' => 2500,
                'downline_commission' => 20,
                'description' => 'Professional level with high referral and team commissions.',
            ],
            [
                'id' => 4,
                'name' => 'Elite Partner',
                'price' => 9999,
                'referral_commission' => 5000,
                'downline_commission' => 30,
                'description' => 'The ultimate partnership with maximum earning potential.',
            ],
        ];

        return responseSuccess('partner_plans', ['Partner plans retrieved successfully'], [
            'data' => $plans,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function joinPartnerProgram(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'plan_id' => 'required|integer|in:1,2,3,4',
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush',
        ]);

        $planPrices = [1 => 1999, 2 => 3999, 3 => 5999, 4 => 9999];
        $planPrice = $planPrices[$request->plan_id];

        $trx = getTrx();
        $gateway = $request->input('gateway', 'watchpay');

        $gw = \App\Models\Gateway::where('alias', $gateway)->first();
        if (!$gw || $gw->status != 1) {
            return responseError('gateway_unavailable', ['Selected payment gateway is currently unavailable.']);
        }

        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $cacheKey = $cachePrefix . $trx;
        cache()->put($cacheKey, [
            'type' => 'partner_program',
            'user_id' => $user->id,
            'plan_id' => (int) $request->plan_id,
            'amount' => (float) $planPrice,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $gw_param = 'watchpay_trx=';
        if ($gateway === 'simplypay') $gw_param = 'simplypay_trx=';
        if ($gateway === 'rupeerush') $gw_param = 'rupeerush_trx=';
        
        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/user/partner-program?' . $gw_param . urlencode($trx) . '&partner_plan_id=' . (int) $request->plan_id;
        $notifyUrl = $base . '/ipn/' . $gateway;

        try {
            if ($gateway === 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => $planPrice,
                    'notifyUrl' => $notifyUrl,
                    'returnUrl' => $pageUrl,
                    'name' => $user->fullname ?: $user->username,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'attach' => 'Partner Program',
                ]);
                $paymentUrl = $sp['pay_link'];
            } elseif ($gateway === 'rupeerush') {
                $ap = \App\Lib\RupeeRushGateway::createPayment([
                    'outTradeNo' => $trx,
                    'totalAmount' => $planPrice,
                    'notifyUrl' => $notifyUrl,
                    'payViewUrl' => $pageUrl,
                    'payName' => $user->fullname ?: $user->username,
                    'payEmail' => $user->email,
                    'payPhone' => $user->mobile,
                ]);
                $paymentUrl = $ap['pay_link'];
            } else {
                $wp = \App\Lib\WatchPayGateway::createWebPayment(
                    $trx,
                    (float) $planPrice,
                    'Partner Program',
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            return responseError('payment_gateway_error', ['Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $paymentUrl,
            'trx' => $trx,
            'amount' => (float) $planPrice,
            'plan_id' => (int) $request->plan_id,
            'gateway_name' => ($gateway === 'simplypay' ? 'SimplyPay' : ($gateway === 'rupeerush' ? 'RupeeRush' : 'WatchPay')),
        ]);
    }

    public function confirmPayment(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'trx' => 'required|string',
            'plan_id' => 'required|integer|in:1,2,3,4',
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush',
        ]);

        $trx = (string) $request->trx;
        $gateway = $request->input('gateway', 'watchpay');
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $cacheKey = $cachePrefix . $trx;

        $session = cache()->get($cacheKey);
        if (!is_array($session) || ($session['type'] ?? '') !== 'partner_program' || (int)($session['user_id'] ?? 0) !== (int)$user->id) {
            return responseError('payment_not_found', ['Payment session not found. Please initiate payment again.']);
        }
        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        $planId = (int) $request->plan_id;
        $planPrices = [1 => 1999, 2 => 3999, 3 => 5999, 4 => 9999];
        $planPrice = (float) $planPrices[$planId];

        // Idempotent: if already active, return ok
        if ((int)($user->partner_plan_id ?? 0) === $planId && $user->partner_plan_valid_until && now()->lt($user->partner_plan_valid_until)) {
            return responseSuccess('partner_program_joined', ['Partner program already active']);
        }

        $user->partner_plan_id = $planId;
        $user->partner_plan_valid_until = now()->addDays(30);
        $user->save();

        // Transaction log (no wallet deduction)
        $transaction = new \App\Models\Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $planPrice;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Join Partner Program via ' . ($gateway == 'simplypay' ? 'SimplyPay' : ($gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay'));
        $transaction->trx = $trx;
        $transaction->remark = 'partner_program_gateway';
        $transaction->save();

        // Agent commission
        try {
            $agentId = (int) ($user->ref_by ?? 0);
            if ($agentId > 0) {
                \App\Lib\AgentCommission::process(
                    $agentId,
                    'partner',
                    (float) $planPrice,
                    $trx,
                    'Agent commission from User#' . $user->id . ' (Partner Program) | Base: ₹' . $planPrice
                );
            }
        } catch (\Throwable $e) {}

        return responseSuccess('partner_program_joined', ['Partner program joined successfully']);
    }
}
