<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdPackage;
use App\Models\AdPackageOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdPlanController extends Controller
{
    public function getAdPlans()
    {
        $general = gs();

        // Define 4 specific ad plans as per requirements
        $adPlans = [
            [
                'id' => 1,
                'name' => 'Starter Plan',
                'price' => 2999,
                'ads_count' => 10,
                'validity_days' => 7,
                'total_earning' => 50000, // 10 ads * 5000 per ad
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => false,
            ],
            [
                'id' => 2,
                'name' => 'Popular Plan',
                'price' => 4999,
                'ads_count' => 25,
                'validity_days' => 15,
                'total_earning' => 125000, // 25 ads * 5000 per ad
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => true,
            ],
            [
                'id' => 3,
                'name' => 'Premium Plan',
                'price' => 7499,
                'ads_count' => 50,
                'validity_days' => 30,
                'total_earning' => 250000, // 50 ads * 5000 per ad
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => false,
            ],
            [
                'id' => 4,
                'name' => 'Elite Plan',
                'price' => 9999,
                'ads_count' => 100,
                'validity_days' => 60,
                'total_earning' => 500000, // 100 ads * 5000 per ad
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => false,
            ],
        ];

        return responseSuccess('ad_plans', ['Ad plans retrieved successfully'], [
            'data' => $adPlans,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function purchaseAdPlan(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'plan_id' => 'required|integer|in:1,2,3,4',
        ]);

        $plansData = [
            1 => ['name' => 'Starter Plan', 'price' => 2999],
            2 => ['name' => 'Popular Plan', 'price' => 4999],
            3 => ['name' => 'Premium Plan', 'price' => 7499],
            4 => ['name' => 'Elite Plan', 'price' => 9999],
        ];

        $planId = $request->plan_id;
        $plan = $plansData[$planId] ?? null;

        if (!$plan) {
            return responseError('plan_not_found', ['Ad plan not found']);
        }

        if ($user->balance < $plan['price']) {
            return responseError('insufficient_balance', ['Insufficient balance']);
        }

        // Deduct balance
        $user->balance -= $plan['price'];
        $user->save();

        // Create ad package order (using plan_id as package_id for tracking)
        $order = AdPackageOrder::create([
            'user_id' => $user->id,
            'package_id' => $planId,
            'amount' => $plan['price'],
            'status' => 1, // Auto-approve for balance payment
            'expires_at' => now()->addDays(30), // 30 days validity
        ]);

        // Create transaction
        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $plan['price'];
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Purchase ad plan: ' . $plan['name'];
        $transaction->trx = $trx;
        $transaction->remark = 'ad_plan_purchase';
        $transaction->save();

        return responseSuccess('ad_plan_purchased', ['Ad plan purchased successfully'], [
            'order_id' => $order->id,
            'expires_at' => $order->expires_at,
        ]);
    }
}
