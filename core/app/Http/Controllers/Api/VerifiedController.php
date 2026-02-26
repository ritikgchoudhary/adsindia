<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\AdminNotification;

class VerifiedController extends Controller
{
    public function purchase(Request $request)
    {
        $user = auth()->user();
        $settings = gs()->beta_verified_settings ?: ['price' => 99];
        $price = $settings['price'] ?? 99;

        if ($user->verified_badge) {
            return response()->json([
                'status' => 'error',
                'message' => ['You are already verified.']
            ], 400);
        }

        if ($user->balance < $price) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient balance. You need ₹' . $price . ' in your main wallet.']
            ], 400);
        }

        // Deduct balance
        $user->balance -= $price;
        $user->verified_badge = true;
        $user->save();

        // Transaction
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $price;
        $transaction->post_balance = $user->balance;
        $transaction->trx_type = '-';
        $transaction->details = 'Purchased Verified Badge (Point #3)';
        $transaction->trx = getTrx();
        $transaction->remark = 'verified_badge';
        $transaction->wallet = 'main';
        $transaction->save();

        // Admin Notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = $user->username . ' purchased Verified Badge';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Verified Badge activated successfully!'],
            'data' => [
                'post_balance' => $user->balance
            ]
        ]);
    }
}
