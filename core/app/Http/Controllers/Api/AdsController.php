<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdPackageOrder;
use App\Models\AdView;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    public function getAds()
    {
        $user = auth()->user();
        $general = gs();

        // Get active ad package order
        $activeOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->first();

        if (!$activeOrder) {
            return responseError('no_active_package', ['You need to purchase an ad package first']);
        }

        // Get today's ad views count
        $todayViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->count();

        // Check if daily limit reached
        $canWatchMore = $todayViews < $activeOrder->package->daily_ad_limit;

        // Generate ads list - Each ad gives 5-6k, total 10-12k in 1 hour (2 ads)
        $ads = [];
        $remainingAds = $activeOrder->package->daily_ad_limit - $todayViews;
        
        // Each ad earns 5000-6000 randomly
        $earnings = [5000, 5500, 6000];
        
        for ($i = 1; $i <= min($remainingAds, $activeOrder->package->daily_ad_limit); $i++) {
            $earning = $earnings[array_rand($earnings)]; // Random earning between 5k-6k
            $ads[] = [
                'id' => $i,
                'title' => 'Ad #' . $i,
                'description' => 'Watch this ad completely (30 minutes) to earn ' . showAmount($earning),
                'image' => '/assets/images/default-ad.jpg',
                'duration' => 30, // 30 minutes as per requirements
                'earning' => (float)$earning,
                'is_active' => $canWatchMore,
                'is_watched' => false,
                'timer' => null,
            ];
        }

        return responseSuccess('ads_data', ['Ads retrieved successfully'], [
            'data' => $ads,
            'active_package' => [
                'name' => $activeOrder->package->name,
                'daily_limit' => $activeOrder->package->daily_ad_limit,
                'today_views' => $todayViews,
                'remaining_ads' => max(0, $remainingAds),
            ],
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function completeAd(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'ad_id' => 'required|integer',
            'watch_duration' => 'required|integer|min:0',
        ]);

        // Get active ad package order
        $activeOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->first();

        if (!$activeOrder) {
            return responseError('no_active_package', ['No active ad package found']);
        }

        // Check if video was watched at least 90% of required duration
        $requiredDuration = $activeOrder->package->duration_seconds;
        $minWatchDuration = (int)($requiredDuration * 0.9);

        if ($request->watch_duration < $minWatchDuration) {
            return responseError('incomplete_watch', ['Please watch the complete video to earn reward']);
        }

        // Check daily limit
        $todayViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->count();

        if ($todayViews >= $activeOrder->package->daily_ad_limit) {
            return responseError('daily_limit_reached', ['Daily ad viewing limit reached']);
        }

        // Each ad earns 5000-6000 randomly
        $earnings = [5000, 5500, 6000];
        $earning = $earnings[array_rand($earnings)];

        // Create ad view record
        $adView = AdView::create([
            'user_id' => $user->id,
            'user_package_id' => $activeOrder->id,
            'ad_url' => $request->ad_url ?? 'https://example.com/ad',
            'reward_amount' => $earning,
            'watch_duration' => $request->watch_duration,
            'is_completed' => true,
            'viewed_at' => now(),
        ]);

        // Add reward to user balance
        $user->balance += $earning;
        $user->save();

        // Create transaction record
        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $earning;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '+';
        $transaction->details = 'Earning from watching ad';
        $transaction->trx = $trx;
        $transaction->remark = 'ad_view_reward';
        $transaction->save();

        return responseSuccess('ad_completed', ['Ad watched successfully! Earning added to your account.'], [
            'earning' => $earning,
            'new_balance' => $user->balance,
        ]);
    }
}
