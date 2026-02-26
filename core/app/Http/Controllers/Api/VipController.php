<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VipPlan;
use App\Models\VipSubscription;
use App\Models\Transaction;
use Illuminate\Http\Request;

class VipController extends Controller
{
    public function info()
    {
        $user = auth()->user();
        $plans = VipPlan::where('enabled', true)->get();
        $subscription = VipSubscription::where('user_id', $user->id)
            ->where('status', true)
            ->where('expires_at', '>', now())
            ->first();

        return responseSuccess('vip_info', ['VIP info retrieved'], [
            'plans' => $plans,
            'subscription' => $subscription ? [
                'plan_id' => $subscription->plan_id,
                'expires_at' => $subscription->expires_at->format('Y-m-d H:i:s'),
                'is_active' => true
            ] : null
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate(['plan_id' => 'required|integer']);
        $user = auth()->user();
        $plan = VipPlan::where('id', $request->plan_id)->where('enabled', true)->firstOrFail();

        if ($user->balance < $plan->price) {
            return responseError('insufficient_balance', ['Insufficient balance to purchase VIP membership']);
        }

        // Deduct balance
        $user->balance -= $plan->price;
        $user->save();

        // Log transaction
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $plan->price;
        $transaction->post_balance = $user->balance;
        $transaction->trx_type = '-';
        $transaction->details = 'Purchased VIP Membership: ' . $plan->name;
        $transaction->trx = getTrx();
        $transaction->remark = 'vip_purchase';
        $transaction->save();

        // Create or Update Subscription
        $subscription = VipSubscription::where('user_id', $user->id)->first() ?? new VipSubscription();
        $subscription->user_id = $user->id;
        $subscription->plan_id = $plan->id;
        
        $currentExpiry = ($subscription->expires_at && $subscription->expires_at > now()) ? $subscription->expires_at : now();
        $subscription->expires_at = $currentExpiry->addMonths($plan->months);
        $subscription->status = true;
        $subscription->save();

        return responseSuccess('subscribed', ['VIP Membership activated successfully!']);
    }
}
