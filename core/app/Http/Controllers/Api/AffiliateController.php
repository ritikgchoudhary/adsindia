<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;

class AffiliateController extends Controller
{
    public function getAffiliateIncome()
    {
        $user = auth()->user();
        $general = gs();

        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Get all referral earnings (50% of referred user's earnings)
        $income = [
            'today' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', ['referral_commission', 'downline_commission', 'affiliate_commission'])
                ->where('trx_type', '+')
                ->where('created_at', '>=', $today)
                ->sum('amount'),
            'this_week' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', ['referral_commission', 'downline_commission', 'affiliate_commission'])
                ->where('trx_type', '+')
                ->where('created_at', '>=', $thisWeek)
                ->sum('amount'),
            'this_month' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', ['referral_commission', 'downline_commission', 'affiliate_commission'])
                ->where('trx_type', '+')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount'),
            'total' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', ['referral_commission', 'downline_commission', 'affiliate_commission'])
                ->where('trx_type', '+')
                ->sum('amount'),
        ];

        // Calculate 50% of referred users' total earnings
        $referredUsers = \App\Models\User::where('ref_by', $user->id)->pluck('id');
        $referredUsersEarnings = Transaction::whereIn('user_id', $referredUsers)
            ->where('trx_type', '+')
            ->sum('amount');
        $affiliateEarning = $referredUsersEarnings * 0.5; // 50% as per requirements

        $history = Transaction::where('user_id', $user->id)
            ->whereIn('remark', ['referral_commission', 'downline_commission', 'affiliate_commission'])
            ->where('trx_type', '+')
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($trx) {
                return [
                    'id' => $trx->id,
                    'created_at' => $trx->created_at->format('Y-m-d H:i:s'),
                    'source' => $trx->remark == 'referral_commission' ? 'Referral' : ($trx->remark == 'affiliate_commission' ? 'Affiliate (50%)' : 'Downline'),
                    'description' => $trx->details,
                    'amount' => $trx->amount,
                ];
            });

        return responseSuccess('affiliate_income', ['Affiliate income retrieved successfully'], [
            'income' => $income,
            'affiliate_earning' => $affiliateEarning, // 50% of referred users' earnings
            'history' => $history,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }
}
