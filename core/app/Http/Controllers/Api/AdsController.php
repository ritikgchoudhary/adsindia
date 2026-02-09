<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdPackageOrder;
use App\Models\AdView;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdsController extends Controller
{
    /**
     * New user = never purchased any ad package. New user gets 2 ads only, 5000 each = 10K total (one-time offer).
     */
    private function isNewUserEligibleForOffer($user)
    {
        $hasAnyPackage = AdPackageOrder::where('user_id', $user->id)->exists();
        $watched = (int) ($user->new_user_ads_watched ?? 0);
        return !$hasAnyPackage && $watched < 2;
    }

    public function getAds()
    {
        $user = auth()->user();
        $general = gs();

        // Get active ad package order
        $activeOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->first();

        // STEP 1: New user offer – no package yet, show 2 ads for 10K total (5000 each)
        if (!$activeOrder) {
            if ($this->isNewUserEligibleForOffer($user)) {
                return $this->getNewUserAds($user, $general);
            }
            return responseError('no_active_package', ['You have completed the new user offer. Purchase an ad plan (₹2999–₹9999) to continue earning.']);
        }

        // Get today's ad views count and watched ad IDs
        $todayViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->where('is_completed', true)
            ->count();

        // Get watched ad IDs for today (to mark as completed)
        // Extract ad_id from ad_url field (format: "ad_id:1|url:...")
        $watchedAdViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->where('is_completed', true)
            ->orderBy('viewed_at', 'asc')
            ->get();
        
        // Extract ad_id from ad_url field
        $watchedAdIds = [];
        $viewIndex = 0;
        foreach ($watchedAdViews as $view) {
            // Try to extract ad_id from ad_url (format: "ad_id:1|url:...")
            if (preg_match('/ad_id:(\d+)/', $view->ad_url, $matches)) {
                $watchedAdIds[] = (int)$matches[1];
            } else {
                // Fallback: use completion order if ad_id not found (for old records)
                $watchedAdIds[] = $viewIndex + 1;
            }
            $viewIndex++;
        }

        // Check if daily limit reached
        $canWatchMore = $todayViews < $activeOrder->package->daily_ad_limit;
        $remainingAds = $activeOrder->package->daily_ad_limit - $todayViews;
        $realAds = $this->fetchRealAdsFromAPI($activeOrder->package->daily_ad_limit);
        $earnings = [5000, 5500, 6000];
        $ads = [];

        for ($i = 1; $i <= $activeOrder->package->daily_ad_limit; $i++) {
            $earning = $earnings[array_rand($earnings)];
            $adData = isset($realAds[$i - 1]) ? $realAds[$i - 1] : $this->getFallbackAd($i);
            $isWatched = in_array($i, $watchedAdIds);
            $ads[] = [
                'id' => $i,
                'title' => $adData['title'],
                'description' => $adData['description'] ?? 'Watch this video ad completely to earn ' . showAmount($earning) . '. Video duration: ' . ($activeOrder->package->duration_seconds / 60) . ' minutes.',
                'video_url' => $adData['video_url'],
                'image' => $adData['image'] ?? '/assets/images/default-ad.jpg',
                'duration' => (int)($activeOrder->package->duration_seconds / 60),
                'duration_seconds' => $activeOrder->package->duration_seconds,
                'earning' => (float)$earning,
                'is_active' => $canWatchMore && !$isWatched,
                'is_watched' => $isWatched,
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
            'is_new_user_offer' => false,
        ]);
    }

    /**
     * New user offer: exactly 2 ads, 5000 each = 10,000 total.
     */
    private function getNewUserAds($user, $general)
    {
        $watched = (int) ($user->new_user_ads_watched ?? 0);
        $realAds = $this->getRealLookingAds(2);
        $ads = [];
        $earningPerAd = 5000;
        $durationSeconds = 60;

        for ($i = 1; $i <= 2; $i++) {
            $isWatched = $watched >= $i;
            $adData = $realAds[$i - 1] ?? $this->getFallbackAd($i);
            $ads[] = [
                'id' => $i,
                'title' => $adData['title'],
                'description' => 'New user offer: Watch this ad completely to earn ' . showAmount($earningPerAd) . '. (2 ads total = ₹10,000.)',
                'video_url' => $adData['video_url'],
                'image' => $adData['image'] ?? '/assets/images/default-ad.jpg',
                'duration' => 1,
                'duration_seconds' => $durationSeconds,
                'earning' => (float) $earningPerAd,
                'is_active' => !$isWatched,
                'is_watched' => $isWatched,
                'timer' => null,
            ];
        }

        return responseSuccess('ads_data', ['New user offer: Watch 2 ads to earn ₹10,000'], [
            'data' => $ads,
            'active_package' => [
                'name' => 'New User Offer',
                'daily_limit' => 2,
                'today_views' => $watched,
                'remaining_ads' => max(0, 2 - $watched),
            ],
            'currency_symbol' => $general->cur_sym ?? '₹',
            'is_new_user_offer' => true,
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

        // New user offer: 2 ads, 5000 each (no package required)
        if (!$activeOrder) {
            if ($this->isNewUserEligibleForOffer($user) && in_array((int)$request->ad_id, [1, 2], true)) {
                return $this->completeNewUserAd($user, $request);
            }
            return responseError('no_active_package', ['No active ad package. Purchase an ad plan to continue.']);
        }

        // Check if video was watched at least 90% of required duration
        $requiredDuration = $activeOrder->package->duration_seconds;
        $minWatchDuration = (int)($requiredDuration * 0.9);
        if ($request->watch_duration < $minWatchDuration) {
            return responseError('incomplete_watch', ['Please watch the complete video to earn reward']);
        }

        // Check if this specific ad (by ID) was already watched today (prevent duplicate)
        $watchedAdViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->where('is_completed', true)
            ->get();
        
        // Check if this ad_id was already watched
        foreach ($watchedAdViews as $view) {
            if (preg_match('/ad_id:(\d+)/', $view->ad_url, $matches)) {
                if ((int)$matches[1] === (int)$request->ad_id) {
                    return responseError('already_watched', ['This ad has already been watched today']);
                }
            }
        }

        // Check daily limit
        $todayViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->where('is_completed', true)
            ->count();

        if ($todayViews >= $activeOrder->package->daily_ad_limit) {
            return responseError('daily_limit_reached', ['Daily ad viewing limit reached']);
        }

        // Each ad earns 5000-6000 randomly
        $earnings = [5000, 5500, 6000];
        $earning = $earnings[array_rand($earnings)];

        // Store ad_id in ad_url field as JSON or prefix for tracking
        // Format: "ad_id:1|url:https://..."
        $adUrlWithId = 'ad_id:' . $request->ad_id . '|url:' . ($request->ad_url ?? 'https://example.com/ad');
        
        // Create ad view record
        $adView = AdView::create([
            'user_id' => $user->id,
            'user_package_id' => $activeOrder->id,
            'ad_url' => $adUrlWithId,
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

    /**
     * Complete one of the 2 new-user ads (5000 each). Next step: KYC then withdraw.
     */
    private function completeNewUserAd($user, Request $request)
    {
        $watched = (int) ($user->new_user_ads_watched ?? 0);
        $expectedAdId = $watched + 1;
        if ((int)$request->ad_id !== $expectedAdId) {
            return responseError('invalid_ad_order', ['Please watch ad ' . $expectedAdId . ' next.']);
        }

        $minWatchDuration = 54; // 90% of 60 seconds
        if ($request->watch_duration < $minWatchDuration) {
            return responseError('incomplete_watch', ['Please watch the complete video to earn reward']);
        }

        $earning = 5000;
        $user->balance = ($user->balance ?? 0) + $earning;
        $user->new_user_ads_watched = $watched + 1;
        $user->save();

        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $earning;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '+';
        $transaction->details = 'New user offer: Earning from watching ad ' . $expectedAdId . '/2';
        $transaction->trx = $trx;
        $transaction->remark = 'ad_view_reward';
        $transaction->save();

        return responseSuccess('ad_completed', ['Ad watched! You earned ₹5,000. ' . ($user->new_user_ads_watched >= 2 ? 'Complete KYC and withdraw your ₹10,000.' : 'Watch the next ad to complete ₹10,000.')], [
            'earning' => $earning,
            'new_balance' => $user->balance,
            'is_new_user_offer' => true,
            'ads_watched' => $user->new_user_ads_watched,
        ]);
    }

    /**
     * Fetch real ads from external API
     */
    private function fetchRealAdsFromAPI($count = 2)
    {
        $ads = [];
        
        try {
            // Option 1: Try to fetch from a real ads API service
            // You can integrate with services like:
            // - Google Ad Manager API
            // - OpenX
            // - AdColony
            // - Or your own ad server
            
            // For now, using a service that provides real advertisement videos
            // This is a placeholder - replace with your actual ads API endpoint
            
            // Example: Fetch from a real ads API
            $response = Http::timeout(5)->get('https://api.example-ads.com/v1/videos', [
                'count' => $count,
                'format' => 'mp4',
                'category' => 'commercial'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['ads']) && is_array($data['ads'])) {
                    foreach ($data['ads'] as $ad) {
                        $ads[] = [
                            'title' => $ad['title'] ?? 'Advertisement',
                            'description' => $ad['description'] ?? 'Watch this advertisement',
                            'video_url' => $ad['video_url'] ?? $ad['url'],
                            'image' => $ad['thumbnail'] ?? $ad['image'],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch ads from API: ' . $e->getMessage());
        }
        
        // If API fails, use real-looking ad videos from public sources
        if (empty($ads)) {
            $ads = $this->getRealLookingAds($count);
        }
        
        return $ads;
    }

    /**
     * Get real-looking advertisement videos
     * These are actual commercial/advertisement videos from public sources
     */
    private function getRealLookingAds($count = 2)
    {
        // Videos exactly 1 minute (60 seconds) for testing
        // Using videos that are approximately 60 seconds long
        $realAdVideos = [
            [
                'title' => 'Test Ad Video 1',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'image' => 'https://picsum.photos/640/360?random=1',
            ],
            [
                'title' => 'Test Ad Video 2',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'image' => 'https://picsum.photos/640/360?random=2',
            ],
            [
                'title' => 'Test Ad Video 3',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'image' => 'https://picsum.photos/640/360?random=3',
            ],
            [
                'title' => 'Test Ad Video 4',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'image' => 'https://picsum.photos/640/360?random=4',
            ],
            [
                'title' => 'Test Ad Video 5',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
                'image' => 'https://picsum.photos/640/360?random=5',
            ],
            [
                'title' => 'Test Ad Video 6',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'image' => 'https://picsum.photos/640/360?random=6',
            ],
            [
                'title' => 'Test Ad Video 7',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreet.mp4',
                'image' => 'https://picsum.photos/640/360?random=7',
            ],
            [
                'title' => 'Test Ad Video 8',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'image' => 'https://picsum.photos/640/360?random=8',
            ],
        ];
        
        // Shuffle and return requested count
        shuffle($realAdVideos);
        return array_slice($realAdVideos, 0, $count);
    }

    /**
     * Get fallback ad if API fails
     */
    private function getFallbackAd($index)
    {
        // 1-minute (60 seconds) test video for fallback
        $fallbackAds = [
            [
                'title' => 'Test Ad Video',
                'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'image' => '/assets/images/default-ad.jpg',
            ],
        ];
        
        return $fallbackAds[0] ?? [
            'title' => 'Test Ad Video #' . $index,
            'description' => 'Watch this 1-minute (60 seconds) test video to earn rewards',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
            'image' => '/assets/images/default-ad.jpg',
        ];
    }
}
