<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function getLeaderboard(Request $request)
    {
        $type = $request->get('type', 'weekly'); // weekly, monthly, alltime
        $general = gs();

        $query = User::where('status', 1);

        switch ($type) {
            case 'weekly':
                $startDate = now()->startOfWeek();
                $query->withSum(['transactions' => function ($q) use ($startDate) {
                    $q->where('trx_type', '+')
                      ->where('created_at', '>=', $startDate);
                }], 'amount');
                break;
            case 'monthly':
                $startDate = now()->startOfMonth();
                $query->withSum(['transactions' => function ($q) use ($startDate) {
                    $q->where('trx_type', '+')
                      ->where('created_at', '>=', $startDate);
                }], 'amount');
                break;
            case 'alltime':
            default:
                $query->withSum(['transactions' => function ($q) {
                    $q->where('trx_type', '+');
                }], 'amount');
                break;
        }

        $leaderboard = $query->orderBy('transactions_sum_amount', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'username' => $user->username,
                    'earning' => (float)($user->transactions_sum_amount ?? 0),
                ];
            });

        return responseSuccess('leaderboard', ['Leaderboard retrieved successfully'], [
            'data' => $leaderboard,
            'type' => $type,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }
}
