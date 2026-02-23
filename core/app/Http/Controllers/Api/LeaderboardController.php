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
        $type = $request->get('type', 'weekly'); // weekly, monthly, alltime
        $general = gs();

        // Date filter
        $startDate = null;
        if ($type === 'weekly') {
            $startDate = now()->startOfWeek();
        } elseif ($type === 'monthly') {
            $startDate = now()->startOfMonth();
        }

        // Ads income = sum of ad_view_reward transactions (credits only)
        $trxAgg = Transaction::query()
            ->select('user_id', DB::raw('SUM(amount) as ads_income'))
            ->where('trx_type', '+')
            ->where('remark', 'ad_view_reward')
            ->when($startDate, function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->groupBy('user_id');

        // Conversion income = sum of paid conversion payouts
        $convAgg = Conversion::query()
            ->select('user_id', DB::raw('SUM(user_payout) as conversion_income'))
            ->where('is_paid', Status::PAID)
            ->when($startDate, function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->groupBy('user_id');

        $rows = User::query()
            ->where('status', Status::USER_ACTIVE)
            ->leftJoinSub($trxAgg, 'trx', function ($join) {
                $join->on('users.id', '=', 'trx.user_id');
            })
            ->leftJoinSub($convAgg, 'conv', function ($join) {
                $join->on('users.id', '=', 'conv.user_id');
            })
            ->select([
                'users.id',
                'users.username',
                'users.firstname',
                'users.lastname',
                'users.image',
                DB::raw('COALESCE(trx.ads_income, 0) as ads_income'),
                DB::raw('COALESCE(conv.conversion_income, 0) as conversion_income'),
                DB::raw('(COALESCE(trx.ads_income, 0) + COALESCE(conv.conversion_income, 0)) as earning'),
            ])
            ->having('earning', '>', 0)
            ->orderByDesc('earning')
            ->orderBy('users.id')
            ->limit(10)
            ->get();

        $leaderboard = $rows->map(function ($user, $index) {
            $name = trim(((string) ($user->firstname ?? '')) . ' ' . ((string) ($user->lastname ?? '')));
            return [
                'rank' => $index + 1,
                'user_id' => (int) $user->id,
                'name' => $name ?: (string) ($user->username ?? ''),
                'username' => $user->username,
                'image' => getImage(getFilePath('userProfile') . '/' . ($user->image ?? ''), getFileSize('userProfile'), true),
                'earning' => (float) ($user->earning ?? 0),
                'ads_income' => (float) ($user->ads_income ?? 0),
                'conversion_income' => (float) ($user->conversion_income ?? 0),
            ];
        })->toArray();

        // Testing: Add dummy data if less than 10
        if (count($leaderboard) < 10) {
            $dummyNames = [
                'Arjun Sharma', 'Priya Patel', 'Rahul Verma', 'Sneha Reddy', 
                'Vikram Singh', 'Ananya Gupta', 'Aditya Mishra', 'Ishani Rao',
                'Karan Malhotra', 'Riya Sen', 'Siddharth Jain', 'Meera Nair'
            ];
            $baseRank = count($leaderboard) + 1;
            $needed = 10 - count($leaderboard);
            for ($i = 0; $i < $needed; $i++) {
                $leaderboard[] = [
                    'rank' => $baseRank + $i,
                    'user_id' => 1000 + $i,
                    'name' => $dummyNames[$i % count($dummyNames)],
                    'username' => '',
                    'image' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($dummyNames[$i % count($dummyNames)]),
                    'earning' => (float) (5000 - ($i * 450)),
                    'ads_income' => (float) (2000),
                    'conversion_income' => (float) (3000 - ($i * 450)),
                ];
            }
            
            // Re-sort mock data by earning
            usort($leaderboard, function($a, $b) {
                return $b['earning'] <=> $a['earning'];
            });
            
            // Re-assign ranks
            foreach ($leaderboard as $idx => &$item) {
                $item['rank'] = $idx + 1;
            }
        }

        // Current user rank (within full set, not just top 100)
        $authUser = auth()->user();
        $current = null;
        if ($authUser) {
            // Compute current earning
            $myAds = Transaction::query()
                ->where('user_id', $authUser->id)
                ->where('trx_type', '+')
                ->where('remark', 'ad_view_reward')
                ->when($startDate, function ($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                })
                ->sum('amount');
            $myConv = Conversion::query()
                ->where('user_id', $authUser->id)
                ->where('is_paid', Status::PAID)
                ->when($startDate, function ($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                })
                ->sum('user_payout');
            $myEarning = (float) ($myAds + $myConv);

            // Rank = number of users with greater earning + 1
            $greaterCount = User::query()
                ->where('status', Status::USER_ACTIVE)
                ->leftJoinSub($trxAgg, 'trx', function ($join) {
                    $join->on('users.id', '=', 'trx.user_id');
                })
                ->leftJoinSub($convAgg, 'conv', function ($join) {
                    $join->on('users.id', '=', 'conv.user_id');
                })
                ->whereRaw('(COALESCE(trx.ads_income, 0) + COALESCE(conv.conversion_income, 0)) > ?', [$myEarning])
                ->count();

            $current = [
                'user_id' => (int) $authUser->id,
                'name' => trim(((string) ($authUser->firstname ?? '')) . ' ' . ((string) ($authUser->lastname ?? ''))) ?: (string) ($authUser->username ?? ''),
                'username' => $authUser->username,
                'image' => getImage(getFilePath('userProfile') . '/' . ($authUser->image ?? ''), getFileSize('userProfile'), true),
                'rank' => (int) $greaterCount + 1,
                'earning' => $myEarning,
                'ads_income' => (float) $myAds,
                'conversion_income' => (float) $myConv,
            ];
        }

        return responseSuccess('leaderboard', ['Leaderboard retrieved successfully'], [
            'rows' => $leaderboard,
            'type' => $type,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'current_user' => $current,
        ]);
    }
}
