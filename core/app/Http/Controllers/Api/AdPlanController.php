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
        $plansData = [
            [
                'name' => 'Starter Plan',
                'slug' => 'starter-plan',
                'price' => 2999,
                'ads_count' => 10,
                'validity_days' => 7,
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => false,
            ],
            [
                'name' => 'Popular Plan',
                'slug' => 'popular-plan',
                'price' => 4999,
                'ads_count' => 25,
                'validity_days' => 15,
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => true,
            ],
            [
                'name' => 'Premium Plan',
                'slug' => 'premium-plan',
                'price' => 7499,
                'ads_count' => 50,
                'validity_days' => 30,
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => false,
            ],
            [
                'name' => 'Elite Plan',
                'slug' => 'elite-plan',
                'price' => 9999,
                'ads_count' => 100,
                'validity_days' => 60,
                'daily_ad_limit' => 2,
                'reward_per_ad' => 5000,
                'duration_minutes' => 30,
                'is_recommended' => false,
            ],
        ];

        // Get or create AdPackage records for each plan
        $adPlans = [];
        foreach ($plansData as $index => $planData) {
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

            $adPlans[] = [
                'id' => $adPackage->id,
                'name' => $adPackage->name,
                'price' => (float)$adPackage->price,
                'ads_count' => $planData['ads_count'],
                'validity_days' => $planData['validity_days'],
                'total_earning' => $planData['ads_count'] * $planData['reward_per_ad'],
                'daily_ad_limit' => $adPackage->daily_ad_limit,
                'reward_per_ad' => (float)$adPackage->reward_per_ad,
                'duration_minutes' => $planData['duration_minutes'],
                'is_recommended' => $adPackage->is_recommended,
            ];
        }

        return responseSuccess('ad_plans', ['Ad plans retrieved successfully'], [
            'data' => $adPlans,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function purchaseAdPlan(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'plan_id' => 'required|integer',
        ]);

        // Get the AdPackage by ID
        $adPackage = AdPackage::where('id', $request->plan_id)
            ->where('status', 1)
            ->first();

        if (!$adPackage) {
            return responseError('plan_not_found', ['Ad plan not found']);
        }

        // Check balance
        if ($user->balance < $adPackage->price) {
            return responseError('insufficient_balance', ['Insufficient balance. Required: ' . showAmount($adPackage->price)]);
        }

        // Calculate validity days based on plan price
        $validityDays = 7; // Default
        if ($adPackage->price == 2999) $validityDays = 7;
        elseif ($adPackage->price == 4999) $validityDays = 15;
        elseif ($adPackage->price == 7499) $validityDays = 30;
        elseif ($adPackage->price == 9999) $validityDays = 60;

        // Deduct balance
        $user->balance -= $adPackage->price;
        $user->save();

        // Create ad package order - this will allow user to watch ads and earn
        $order = AdPackageOrder::create([
            'user_id' => $user->id,
            'package_id' => $adPackage->id, // Link to AdPackage
            'amount' => $adPackage->price,
            'status' => 1, // Auto-approve for balance payment
            'expires_at' => now()->addDays($validityDays),
        ]);

        // Create transaction
        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $adPackage->price;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Purchase ad plan: ' . $adPackage->name;
        $transaction->trx = $trx;
        $transaction->remark = 'ad_plan_purchase';
        $transaction->save();

        return responseSuccess('ad_plan_purchased', ['Ad plan purchased successfully! You can now watch ads and earn.'], [
            'order_id' => $order->id,
            'expires_at' => $order->expires_at->format('Y-m-d H:i:s'),
            'package_name' => $adPackage->name,
            'daily_ad_limit' => $adPackage->daily_ad_limit,
            'reward_per_ad' => $adPackage->reward_per_ad,
        ]);
    }
}
