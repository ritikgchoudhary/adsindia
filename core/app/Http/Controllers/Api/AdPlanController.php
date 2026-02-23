<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\AgentCommission;
use App\Models\AdPackage;
use App\Models\AdPackageOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdPlanController extends Controller
{
    public function getAdPlans()
    {
        $general = gs();

        // Earning range per ad (shown in UI). Actual earning is generated when completing each ad.
        $mode = (string) ($general->ads_reward_mode ?? 'random'); // random|fixed
        $mult = (float) ($general->ads_reward_multiplier ?? 1);
        $min = (float) ($general->ads_reward_min ?? 1000);
        $max = (float) ($general->ads_reward_max ?? 5000);
        $fixed = (float) ($general->ads_reward_fixed ?? 0);

        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        $rewardMin = $mode === 'fixed' ? $fixed : $min;
        $rewardMax = $mode === 'fixed' ? $fixed : $max;
        $rewardMin = (float) ($rewardMin * $mult);
        $rewardMax = (float) ($rewardMax * $mult);
        if ($rewardMin > $rewardMax) {
            [$rewardMin, $rewardMax] = [$rewardMax, $rewardMin];
        }

        // Define 4 specific ad plans as per requirements
        $plansData = [
            [
                'name' => 'Starter Plan',
                'slug' => 'starter-plan',
                'price' => 2999,
                'ads_count' => 750, // 25 ads * 30 days
                'validity_days' => 30,
                'daily_ad_limit' => 25,
                'reward_per_ad' => 30,
                'duration_minutes' => 1,
                'is_recommended' => false,
            ],
            [
                'name' => 'Popular Plan',
                'slug' => 'popular-plan',
                'price' => 4999,
                'ads_count' => 3600, // 60 ads * 60 days
                'validity_days' => 60,
                'daily_ad_limit' => 60,
                'reward_per_ad' => 33.33,
                'duration_minutes' => 1,
                'is_recommended' => true,
            ],
            [
                'name' => 'Premium Plan',
                'slug' => 'premium-plan',
                'price' => 7499,
                'ads_count' => 18000, // 100 ads * 180 days
                'validity_days' => 180,
                'daily_ad_limit' => 100,
                'reward_per_ad' => 40,
                'duration_minutes' => 1,
                'is_recommended' => false,
            ],
            [
                'name' => 'Elite Plan',
                'slug' => 'elite-plan',
                'price' => 9999,
                'ads_count' => 73000, // 200 ads * 365 days
                'validity_days' => 365,
                'daily_ad_limit' => 200,
                'reward_per_ad' => 50,
                'duration_minutes' => 1,
                'is_recommended' => false,
            ],
        ];

        // Get or create AdPackage records for each plan
        $adPlans = [];
        foreach ($plansData as $index => $planData) {
            try {
                $adPackage = AdPackage::firstOrCreate(
                    ['slug' => $planData['slug']],
                    [
                        'name' => $planData['name'],
                        'description' => $planData['name'] . ' - ' . $planData['ads_count'] . ' ads, valid for ' . $planData['validity_days'] . ' days',
                        'price' => $planData['price'],
                        'daily_ad_limit' => $planData['daily_ad_limit'],
                        'reward_per_ad' => $planData['reward_per_ad'],
                        'duration_seconds' => $planData['duration_minutes'] * 60, // Convert to seconds
                        'is_recommended' => $planData['is_recommended'],
                        'status' => 1,
                        'sort_order' => $index + 1,
                    ]
                );

                // Update if exists
                if ($adPackage->wasRecentlyCreated === false) {
                    $adPackage->update([
                        'name' => $planData['name'],
                        'price' => $planData['price'],
                        'daily_ad_limit' => $planData['daily_ad_limit'],
                        'reward_per_ad' => $planData['reward_per_ad'],
                        'duration_seconds' => $planData['duration_minutes'] * 60,
                        'is_recommended' => $planData['is_recommended'],
                        'status' => 1,
                    ]);
                }

                $rewardPerAdMax = (float) $planData['reward_per_ad'];
                $rewardPerAdMin = round($rewardPerAdMax * 0.5, 2); // Show a range starting from 50%

                $adPlans[] = [
                    'id' => $adPackage->id,
                    'name' => $adPackage->name,
                    'price' => (float)$adPackage->price,
                    'ads_count' => $planData['ads_count'],
                    'validity_days' => $planData['validity_days'],
                    'reward_per_ad_min' => (float) $rewardPerAdMin,
                    'reward_per_ad_max' => (float) $rewardPerAdMax,
                    'total_earning_min' => (float) ($planData['ads_count'] * $rewardPerAdMin),
                    'total_earning_max' => (float) ($planData['ads_count'] * $rewardPerAdMax),
                    // Backward compatibility
                    'total_earning' => (float) ($planData['ads_count'] * $rewardPerAdMax),
                    'daily_ad_limit' => $adPackage->daily_ad_limit,
                    'daily_earning_max' => (float) ($rewardPerAdMax * $adPackage->daily_ad_limit),
                    'reward_per_ad' => (float) $rewardPerAdMax,
                    'duration_minutes' => $planData['duration_minutes'],
                    'is_recommended' => $adPackage->is_recommended,
                ];
            } catch (\Exception $e) {
                // If database operation fails, return plan data directly
                \Log::error('Error creating AdPackage: ' . $e->getMessage());
                $rewardPerAdMax = (float) $planData['reward_per_ad'];
                $rewardPerAdMin = round($rewardPerAdMax * 0.5, 2);

                $adPlans[] = [
                    'id' => $index + 1, // Use index as ID if database fails
                    'name' => $planData['name'],
                    'price' => (float)$planData['price'],
                    'ads_count' => $planData['ads_count'],
                    'validity_days' => $planData['validity_days'],
                    'reward_per_ad_min' => (float) $rewardPerAdMin,
                    'reward_per_ad_max' => (float) $rewardPerAdMax,
                    'total_earning_min' => (float) ($planData['ads_count'] * $rewardPerAdMin),
                    'total_earning_max' => (float) ($planData['ads_count'] * $rewardPerAdMax),
                    'total_earning' => (float) ($planData['ads_count'] * $rewardPerAdMax),
                    'daily_ad_limit' => $planData['daily_ad_limit'],
                    'daily_earning_max' => (float) ($rewardPerAdMax * $planData['daily_ad_limit']),
                    'reward_per_ad' => (float) $rewardPerAdMax,
                    'duration_minutes' => $planData['duration_minutes'],
                    'is_recommended' => $planData['is_recommended'],
                ];
            }
        }

        // UI text (editable from Master Admin)
        $uiTitle = (string) ($general->ad_plans_info_title ?? '');
        $uiDesc = (string) ($general->ad_plans_info_description ?? '');
        $uiBullets = [];
        try {
            $raw = $general->ad_plans_info_bullets ?? null;
            if ($raw) {
                $decoded = json_decode((string) $raw, true);
                if (is_array($decoded)) $uiBullets = array_values(array_filter($decoded, fn($v) => is_string($v) && trim($v) !== ''));
            }
        } catch (\Throwable $e) {}

        return responseSuccess('ad_plans', ['Ad plans retrieved successfully'], [
            'data' => $adPlans,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'ui' => [
                'info_title' => $uiTitle,
                'info_description' => $uiDesc,
                'info_bullets' => $uiBullets,
                'reward_mode' => $mode,
                'reward_min' => (float) $rewardMin,
                'reward_max' => (float) $rewardMax,
            ],
        ]);
    }

    public function purchaseAdPlan(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'plan_id' => 'required|integer',
            'payment_method' => 'required|in:gateway', // Only gateway payment
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush,custom_qr',
        ]);

        // Get the AdPackage by ID
        $adPackage = AdPackage::where('id', $request->plan_id)
            ->where('status', 1)
            ->first();

        if (!$adPackage) {
            return responseError('plan_not_found', ['Ad plan not found']);
        }

        $gateway = $request->input('gateway', 'watchpay');
        $gw = \App\Models\Gateway::where('alias', $gateway)->first();
        if (!$gw || $gw->status != 1) {
            return responseError('gateway_unavailable', ['Selected payment gateway is currently unavailable.']);
        }

        // Only gateway payment is allowed
        return $this->initiateGatewayPayment($user, $adPackage, $gateway);
    }

    /**
     * Process the purchase after payment (balance or gateway success)
     */
    private function processPurchase($user, $adPackage, ?string $gatewayTrx = null)
    {
        // Calculate validity days based on plan price
        $validityDays = 30; // Default
        if ($adPackage->price == 2999) $validityDays = 30;
        elseif ($adPackage->price == 4999) $validityDays = 60;
        elseif ($adPackage->price == 7499) $validityDays = 180;
        elseif ($adPackage->price == 9999) $validityDays = 365;

        // Create ad package order - this will allow user to watch ads and earn
        $order = AdPackageOrder::create([
            'user_id' => $user->id,
            'package_id' => $adPackage->id,
            'amount' => $adPackage->price,
            'status' => 1, // Active
            'expires_at' => now()->addDays($validityDays),
        ]);

        // Create transaction
        $trx = $gatewayTrx ?: getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $adPackage->price;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Purchase ad plan via ' . ($gatewayTrx ? ($request->gateway == 'simplypay' ? 'SimplyPay' : ($request->gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay')) : 'Gateway') . ': ' . $adPackage->name;
        $transaction->trx = $trx;
        $transaction->remark = 'ad_plan_purchase';
        $transaction->save();

        // Agent commission (only if sponsor is Agent) – per-plan override supported via rules table
        try {
            $agentId = (int) ($user->ref_by ?? 0);
            if ($agentId > 0) {
                AgentCommission::process(
                    $agentId,
                    'adplan',
                    (float) ($adPackage->price ?? 0),
                    (string) $trx,
                    'Agent commission from User#' . (int) $user->id . ' – Ad Plan: ' . (string) ($adPackage->name ?? '') . ' | Base: ₹' . (float) ($adPackage->price ?? 0),
                    ['plan_type' => 'ad_plan', 'plan_id' => (int) ($adPackage->id ?? 0)]
                );
            }
        } catch (\Throwable $e) {
            // non-blocking
        }

        return responseSuccess('ad_plan_purchased', ['Ad plan purchased successfully! You can now watch ads and earn.'], [
            'order_id' => $order->id,
            'expires_at' => $order->expires_at->format('Y-m-d H:i:s'),
            'package_name' => $adPackage->name,
            'daily_ad_limit' => $adPackage->daily_ad_limit,
            'reward_per_ad' => $adPackage->reward_per_ad,
        ]);
    }

    /**
     * Initiate dummy gateway payment
     */
    private function initiateGatewayPayment($user, $adPackage, string $gateway = 'watchpay')
    {
        $trx = getTrx();

        // 🟢 Create a real Deposit record so it shows in "All Orders" immediately (Initiated)
        $gateRecord = \App\Models\Gateway::where('alias', $gateway)->first();
        $deposit = new \App\Models\Deposit();
        $deposit->user_id = $user->id;
        $deposit->method_code = $gateRecord->code ?? 0;
        $deposit->method_currency = 'INR';
        $deposit->amount = (float) $adPackage->price;
        $deposit->charge = 0;
        $deposit->rate = 1;
        $deposit->final_amount = (float) $adPackage->price;
        $deposit->trx = $trx;
        $deposit->remark = 'ad_plan_purchase';
        $deposit->detail = ['plan_id' => $adPackage->id]; // store for later fulfillment
        $deposit->status = \App\Constants\Status::PAYMENT_INITIATE;
        $deposit->save();

        // Create a payment session for gateway IPN to update
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $cacheKey = $cachePrefix . $trx;
        cache()->put($cacheKey, [
            'type' => 'ad_plan',
            'user_id' => $user->id,
            'plan_id' => $adPackage->id,
            'amount' => (float) $adPackage->price,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        // Page return to SPA payment screen to confirm
        $gw_param = 'watchpay_trx=';
        if ($gateway === 'simplypay') $gw_param = 'simplypay_trx=';
        if ($gateway === 'rupeerush') $gw_param = 'rupeerush_trx=';
        
        $base = request()->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/user/ad-plans/payment?' . $gw_param . urlencode($trx) . '&plan_id=' . $adPackage->id . '&amount=' . $adPackage->price . '&plan_name=' . urlencode($adPackage->name);
        $notifyUrl = $base . '/ipn/' . $gateway;
        try {
            if ($gateway === 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => $adPackage->price,
                    'notifyUrl' => $notifyUrl,
                    'returnUrl' => $pageUrl,
                    'name' => $user->fullname ?: $user->username,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'attach' => 'Ad Plan: ' . $adPackage->name,
                ]);
                $paymentUrl = $sp['pay_link'];
            } elseif ($gateway === 'rupeerush') {
                $ap = \App\Lib\RupeeRushGateway::createPayment([
                    'outTradeNo' => $trx,
                    'totalAmount' => $adPackage->price,
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
                    (float) $adPackage->price,
                    'Ad Plan: ' . $adPackage->name,
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            \Log::error('Payment gateway error: ' . $e->getMessage());
            return responseError('payment_gateway_error', [$e->getMessage()]);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $paymentUrl,
            'trx' => $trx,
            'amount' => $adPackage->price,
            'gateway_name' => ($gateway === 'simplypay' ? 'SimplyPay' : ($gateway === 'rupeerush' ? 'RupeeRush' : 'WatchPay')),
        ]);
    }

    /**
     * Dummy Gateway Payment Handler
     * This simulates payment processing
     */
    public function dummyGatewayPayment(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
            'plan_id' => 'required|integer',
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush',
        ]);

        $user = auth()->user();
        $planId = $request->plan_id;
        $gateway = $request->input('gateway', 'watchpay');

        // Get the AdPackage
        $adPackage = AdPackage::where('id', $planId)
            ->where('status', 1)
            ->first();

        if (!$adPackage) {
            return responseError('plan_not_found', ['Ad plan not found']);
        }

        // Gateway verification
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $cacheKey = $cachePrefix . $request->trx;
        $session = cache()->get($cacheKey);
        if (!is_array($session) || ($session['type'] ?? '') !== 'ad_plan' || (int)($session['user_id'] ?? 0) !== (int)$user->id) {
            return responseError('payment_not_found', ['Payment session not found. Please initiate payment again.']);
        }
        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        // Gateway verified: activate plan without using wallet
        return $this->processPurchase($user, $adPackage, (string) $request->trx);
    }
}
