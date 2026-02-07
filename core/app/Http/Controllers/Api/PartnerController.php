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
                'name' => 'Basic Partner',
                'price' => 2000,
                'referral_commission' => 1000,
                'downline_commission' => 0,
                'description' => 'Get ₹1,000 per referral',
            ],
            [
                'id' => 2,
                'name' => 'Premium Partner',
                'price' => 4000,
                'referral_commission' => 1500,
                'downline_commission' => 20,
                'description' => 'Get ₹1,500 per referral + 20% of downline earnings',
            ],
            [
                'id' => 3,
                'name' => 'Elite Partner',
                'price' => 6000,
                'referral_commission' => 2500,
                'downline_commission' => 30,
                'description' => 'Get ₹2,500 per referral + 30% of downline earnings',
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
            'plan_id' => 'required|integer|in:1,2,3',
        ]);

        $planPrices = [1 => 2000, 2 => 4000, 3 => 6000];
        $planPrice = $planPrices[$request->plan_id];

        if ($user->balance < $planPrice) {
            return responseError('insufficient_balance', ['Insufficient balance']);
        }

        // Deduct balance
        $user->balance -= $planPrice;
        $user->partner_plan_id = $request->plan_id;
        $user->partner_plan_valid_until = now()->addDays(30);
        $user->save();

        // Create transaction
        $trx = getTrx();
        $transaction = new \App\Models\Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $planPrice;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Join Partner Program';
        $transaction->trx = $trx;
        $transaction->remark = 'partner_program';
        $transaction->save();

        return responseSuccess('partner_program_joined', ['Partner program joined successfully']);
    }
}
