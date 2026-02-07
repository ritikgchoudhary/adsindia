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
        foreach ($watchedAdViews as $view) {
            // Try to extract ad_id from ad_url (format: "ad_id:1|url:...")
            if (preg_match('/ad_id:(\d+)/', $view->ad_url, $matches)) {
                $watchedAdIds[] = (int)$matches[1];
            } else {
                // Fallback: use completion order if ad_id not found (for old records)
                $watchedAdIds[] = count($watchedAdIds) + 1;
            }
        }
        
        // Debug logging
        \Log::info("Watched ad IDs extracted: " . json_encode($watchedAdIds) . " from " . $watchedAdViews->count() . " views");

        // Check if daily limit reached
        $canWatchMore = $todayViews < $activeOrder->package->daily_ad_limit;

        // Generate random video ads - Each ad gives 5-6k
        $ads = [];
        $remainingAds = $activeOrder->package->daily_ad_limit - $todayViews;
        
        // Fetch real ads from external API
        $realAds = $this->fetchRealAdsFromAPI($activeOrder->package->daily_ad_limit);
        
        // Each ad earns 5000-6000 randomly
        $earnings = [5000, 5500, 6000];
        
        for ($i = 1; $i <= $activeOrder->package->daily_ad_limit; $i++) {
            $earning = $earnings[array_rand($earnings)]; // Random earning between 5k-6k
            
            // Get real ad data or use fallback
            $adData = isset($realAds[$i - 1]) ? $realAds[$i - 1] : $this->getFallbackAd($i);
            
            // Check if this ad has been watched today (by ad ID - position in sequence)
            $isWatched = in_array($i, $watchedAdIds);
            
            // Debug logging (remove in production)
            if ($i <= 3) {
                \Log::info("Ad $i - isWatched: " . ($isWatched ? 'true' : 'false') . ", watchedAdIds: " . json_encode($watchedAdIds));
            }
            
            $ads[] = [
                'id' => $i,
                'title' => $adData['title'],
                'description' => $adData['description'] ?? 'Watch this video ad completely to earn ' . showAmount($earning) . '. Video duration: ' . ($activeOrder->package->duration_seconds / 60) . ' minutes.',
                'video_url' => $adData['video_url'],
                'image' => $adData['image'] ?? '/assets/images/default-ad.jpg',
                'duration' => (int)($activeOrder->package->duration_seconds / 60), // Duration in minutes from package
                'duration_seconds' => $activeOrder->package->duration_seconds, // Duration in seconds
                'earning' => (float)$earning,
                'is_active' => $canWatchMore && !$isWatched,
                'is_watched' => $isWatched, // Explicitly set watched status
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
