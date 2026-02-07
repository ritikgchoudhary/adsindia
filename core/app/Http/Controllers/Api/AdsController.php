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

        // Generate random video ads - Each ad gives 5-6k
        $ads = [];
        $remainingAds = $activeOrder->package->daily_ad_limit - $todayViews;
        
        // Random video URLs (sample videos - replace with actual ad videos)
        $randomVideos = [
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4',
            'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
        ];
        
        // Random ad titles
        $adTitles = [
            'Amazing Product Showcase',
            'Latest Technology Review',
            'Product Demo Video',
            'Brand Advertisement',
            'Special Offer Video',
            'Product Launch Video',
            'Customer Testimonial',
            'How It Works Video',
            'Product Features',
            'Limited Time Offer'
        ];
        
        // Each ad earns 5000-6000 randomly
        $earnings = [5000, 5500, 6000];
        
        for ($i = 1; $i <= min($remainingAds, $activeOrder->package->daily_ad_limit); $i++) {
            $earning = $earnings[array_rand($earnings)]; // Random earning between 5k-6k
            $randomVideo = $randomVideos[array_rand($randomVideos)]; // Random video
            $randomTitle = $adTitles[array_rand($adTitles)]; // Random title
            
            $ads[] = [
                'id' => $i,
                'title' => $randomTitle . ' #' . $i,
                'description' => 'Watch this video ad completely to earn ' . showAmount($earning) . '. Video duration: ' . ($activeOrder->package->duration_seconds / 60) . ' minutes.',
                'video_url' => $randomVideo,
                'image' => '/assets/images/default-ad.jpg',
                'duration' => (int)($activeOrder->package->duration_seconds / 60), // Duration in minutes from package
                'duration_seconds' => $activeOrder->package->duration_seconds, // Duration in seconds
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
