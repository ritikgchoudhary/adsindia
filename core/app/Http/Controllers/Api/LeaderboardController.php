<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Models\Conversion;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function getLeaderboard(Request $request)
    {
        $type = $request->get('type', 'weekly'); 
        $general = \App\Models\GeneralSetting::first();

        // Strict Visibility Check
        $checks = [
            'today' => (bool)$general->lb_show_today,
            'weekly' => (bool)$general->lb_show_weekly,
            'monthly' => (bool)$general->lb_show_monthly,
            'alltime' => (bool)$general->lb_show_all_time,
            'all_time' => (bool)$general->lb_show_all_time,
        ];

        $isEnabled = $checks[$type] ?? false;
        $anyEnabled = $checks['today'] || $checks['weekly'] || $checks['monthly'] || $checks['alltime'];

        if (!$isEnabled || !$anyEnabled) {
            return responseSuccess('leaderboard', ['Leaderboard period is disabled'], [
                'rows' => [],
                'type' => $type,
                'currency_symbol' => $general->cur_sym ?? '₹',
                'current_user' => null,
                'settings' => [
                    'show_today' => (bool)$general->lb_show_today,
                    'show_weekly' => (bool)$general->lb_show_weekly,
                    'show_monthly' => (bool)$general->lb_show_monthly,
                    'show_all_time' => (bool)$general->lb_show_all_time,
                ]
            ]);
        }

        // Date filter
        $startDate = null;
        if ($type === 'today') {
            $startDate = now()->startOfDay();
        } elseif ($type === 'weekly') {
            $startDate = now()->startOfWeek();
        } elseif ($type === 'monthly') {
            $startDate = now()->startOfMonth();
        }

        $affiliateRemarks = [
            'referral_commission',
            'downline_commission',
            'affiliate_commission',
            'direct_affiliate_commission',
            'agent_registration_commission',
            'agent_kyc_commission',
            'agent_withdraw_fee_commission',
            'agent_upgrade_commission',
            'agent_course_commission',
            'agent_adplan_commission',
            'agent_partner_commission',
            'agent_certificate_commission',
            'agent_partner_override_commission',
        ];

        // Ads income
        $adsTrxAgg = Transaction::query()
            ->select('user_id', DB::raw('SUM(amount) as real_ads_income'))
            ->where('trx_type', '+')
            ->where('remark', 'ad_view_reward')
            ->when($startDate, function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->groupBy('user_id');

        // Affiliate income
        $affTrxAgg = Transaction::query()
            ->select('user_id', DB::raw('SUM(amount) as real_aff_income'))
            ->where('trx_type', '+')
            ->whereIn('remark', $affiliateRemarks)
            ->when($startDate, function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->groupBy('user_id');

        // Manual income fields based on type
        $manualAdsField = 'lead_ads_all_time';
        $manualAffField = 'lead_aff_all_time';
        if ($type === 'today') {
            $manualAdsField = 'lead_ads_today';
            $manualAffField = 'lead_aff_today';
        } elseif ($type === 'weekly') {
            $manualAdsField = 'lead_ads_weekly';
            $manualAffField = 'lead_aff_weekly';
        } elseif ($type === 'monthly') {
            $manualAdsField = 'lead_ads_monthly';
            $manualAffField = 'lead_aff_monthly';
        }

        $authUser = auth()->user();

        $users = User::query()
            ->where('status', Status::USER_ACTIVE)
            ->where('is_lb_hidden', 0)
            ->when($authUser && !empty($authUser->branch_id), function($q) use ($authUser) {
                // If user is in a branch, he only sees people from his branch.
                $q->where('branch_id', $authUser->branch_id);
            })
            ->leftJoinSub($adsTrxAgg, 'ads_trx', function ($join) {
                $join->on('users.id', '=', 'ads_trx.user_id');
            })
            ->leftJoinSub($affTrxAgg, 'aff_trx', function ($join) {
                $join->on('users.id', '=', 'aff_trx.user_id');
            })
            ->select([
                'users.id',
                'users.username',
                'users.firstname',
                'users.lastname',
                'users.image',
                DB::raw("COALESCE(ads_trx.real_ads_income, 0) as real_ads_income"),
                DB::raw("COALESCE(aff_trx.real_aff_income, 0) as real_aff_income"),
                DB::raw("users.$manualAdsField as manual_ads_income"),
                DB::raw("users.$manualAffField as manual_aff_income"),
            ])
            ->get();

        // Map and calculate total including manual
        $leaderboardRaw = $users->map(function ($user) {
            $realAds = (float) $user->real_ads_income;
            $realAff = (float) $user->real_aff_income;
            $manualAds = (float) $user->manual_ads_income;
            $manualAff = (float) $user->manual_aff_income;
            
            // Total Earning = Real + Manual
            $totalAds = $realAds + $manualAds;
            $totalAff = $realAff + $manualAff;
            $earning = $totalAds + $totalAff;

            return [
                'user_id' => (int) $user->id,
                'name' => trim(((string) ($user->firstname ?? '')) . ' ' . ((string) ($user->lastname ?? ''))) ?: $user->username,
                'username' => $user->username,
                'image' => getImage(getFilePath('userProfile') . '/' . ($user->image ?? ''), getFileSize('userProfile'), true),
                'earning' => $earning,
                'ads_income' => $totalAds,
                'affiliate_income' => $totalAff,
            ];
        });

        // Filter out zero earnings and sort
        $leaderboard = $leaderboardRaw->filter(function($item) {
            return $item['earning'] > 0;
        })
        ->sortByDesc('earning')
        ->values()
        ->take(10)
        ->map(function($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        // Current user stats
        $current = null;
        if ($authUser) {
            $myRecord = $leaderboardRaw->where('user_id', $authUser->id)->first();
            if ($myRecord) {
                $greaterCount = $leaderboardRaw->where('earning', '>', $myRecord['earning'])->count();
                $myRecord['rank'] = $greaterCount + 1;
                $current = $myRecord;
            } else {
                // If user not in list (0 earning), find them manually
                $current = [
                    'user_id' => (int) $authUser->id,
                    'name' => trim(((string) ($authUser->firstname ?? '')) . ' ' . ((string) ($authUser->lastname ?? ''))) ?: $authUser->username,
                    'username' => $authUser->username,
                    'image' => getImage(getFilePath('userProfile') . '/' . ($authUser->image ?? ''), getFileSize('userProfile'), true),
                    'rank' => 'NR',
                    'earning' => 0,
                    'ads_income' => 0,
                    'affiliate_income' => 0,
                ];
            }
        }

        return responseSuccess('leaderboard', ['Leaderboard retrieved successfully'], [
            'rows' => $leaderboard->values(),
            'type' => $type,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'current_user' => $current,
            'settings' => [
                'show_today' => (bool)$general->lb_show_today,
                'show_weekly' => (bool)$general->lb_show_weekly,
                'show_monthly' => (bool)$general->lb_show_monthly,
                'show_all_time' => (bool)$general->lb_show_all_time,
            ]
        ]);
    }
}
