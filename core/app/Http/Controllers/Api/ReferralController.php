<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\URL;

class ReferralController extends Controller
{
    public function getReferralData()
    {
        $user = auth()->user();
        $general = gs();

        // Generate package-specific referral links
        $packages = [
            ['id' => 1, 'name' => 'AdsLite', 'price' => 1499, 'discount' => 0],
            ['id' => 2, 'name' => 'AdsPro', 'price' => 2999, 'discount' => 499],
            ['id' => 3, 'name' => 'AdsSupreme', 'price' => 5999, 'discount' => 0],
            ['id' => 4, 'name' => 'AdsPremium', 'price' => 9999, 'discount' => 0],
            ['id' => 5, 'name' => 'AdsPremium+', 'price' => 15999, 'discount' => 0],
        ];

        $referralLinks = [];
        foreach ($packages as $pkg) {
            $discountedPrice = $pkg['price'] - $pkg['discount'];
            $referralLinks[] = [
                'package_id' => $pkg['id'],
                'package_name' => $pkg['name'],
                'original_price' => $pkg['price'],
                'discount' => $pkg['discount'],
                'discounted_price' => $discountedPrice,
                'link' => URL::to('/register?ref=' . $user->username . '&pkg=' . $pkg['id']),
            ];
        }

        // General referral link
        $referralLink = URL::to('/register?ref=' . $user->username);

        // Get downline team
        $downlineTeam = User::where('ref_by', $user->id)
            ->latest()
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'username' => $member->username,
                    'email' => $member->email,
                    'joined_at' => $member->created_at->format('Y-m-d H:i:s'),
                    'status' => $member->status == 1 ? 'active' : 'inactive',
                    'earning' => Transaction::where('user_id', $member->id)
                        ->where('trx_type', '+')
                        ->sum('amount'),
                ];
            });

        $teamStats = [
            'total_members' => User::where('ref_by', $user->id)->count(),
            'active_members' => User::where('ref_by', $user->id)->where('status', 1)->count(),
            'total_earning' => Transaction::where('user_id', $user->id)
                ->where('remark', 'referral_commission')
                ->where('trx_type', '+')
                ->sum('amount'),
        ];

        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $referralEarning = [
            'today' => Transaction::where('user_id', $user->id)
                ->where('remark', 'referral_commission')
                ->where('trx_type', '+')
                ->where('created_at', '>=', $today)
                ->sum('amount'),
            'this_month' => Transaction::where('user_id', $user->id)
                ->where('remark', 'referral_commission')
                ->where('trx_type', '+')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount'),
            'total' => Transaction::where('user_id', $user->id)
                ->where('remark', 'referral_commission')
                ->where('trx_type', '+')
                ->sum('amount'),
        ];

        return responseSuccess('referral_data', ['Referral data retrieved successfully'], [
            'referral_link' => $referralLink,
            'package_links' => $referralLinks,
            'downline_team' => $downlineTeam,
            'team_stats' => $teamStats,
            'referral_earning' => $referralEarning,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }
}
