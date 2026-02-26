<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\AdminNotification;
use Carbon\Carbon;

class AdBoosterController extends Controller
{
    public function purchase(Request $request)
    {
        $request->validate([
            'type' => 'required|in:daily,weekly'
        ]);

        $user = auth()->user();
        $type = $request->type;
        
        $settings = gs()->beta_booster_settings ?: ['daily_price' => 29, 'weekly_price' => 149, 'extra_ads' => 5];
        $price = $type === 'daily' ? ($settings['daily_price'] ?? 29) : ($settings['weekly_price'] ?? 149);
        $days = $type === 'daily' ? 1 : 7;

        if ($user->balance < $price) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient balance. You need ₹' . $price . ' in your main wallet.']
            ], 400);
        }

        // Deduct balance
        $user->balance -= $price;
        
        // Update expiry
        $currentExpiry = $user->booster_expires_at ? Carbon::parse($user->booster_expires_at) : null;
        if ($currentExpiry && $currentExpiry->isFuture()) {
            $user->booster_expires_at = $currentExpiry->addDays($days);
        } else {
            $user->booster_expires_at = now()->addDays($days);
        }
        
        $user->save();

        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $price;
        $transaction->post_balance = $user->balance;
        $transaction->trx_type = '-';
        $transaction->details = 'Purchased ' . ucfirst($type) . ' Ad Booster (+5 Ads/day)';
        $transaction->trx = getTrx();
        $transaction->remark = 'ad_booster';
        $transaction->wallet = 'main';
        $transaction->save();

        // Admin Notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = $user->username . ' purchased ' . $type . ' Ad Booster';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'message' => [ucfirst($type) . ' Ad Booster activated successfully!'],
            'data' => [
                'post_balance' => $user->balance,
                'expires_at' => $user->booster_expires_at
            ]
        ]);
    }
}
