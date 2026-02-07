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
        
        // Fetch real ads from external API
        $realAds = $this->fetchRealAdsFromAPI($remainingAds);
        
        // Each ad earns 5000-6000 randomly
        $earnings = [5000, 5500, 6000];
        
        for ($i = 1; $i <= min($remainingAds, $activeOrder->package->daily_ad_limit); $i++) {
            $earning = $earnings[array_rand($earnings)]; // Random earning between 5k-6k
            
            // Get real ad data or use fallback
            $adData = isset($realAds[$i - 1]) ? $realAds[$i - 1] : $this->getFallbackAd($i);
            
            $ads[] = [
                'id' => $i,
                'title' => $adData['title'],
                'description' => $adData['description'] ?? 'Watch this video ad completely to earn ' . showAmount($earning) . '. Video duration: ' . ($activeOrder->package->duration_seconds / 60) . ' minutes.',
                'video_url' => $adData['video_url'],
                'image' => $adData['image'] ?? '/assets/images/default-ad.jpg',
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
        // Real advertisement videos from public sources
        // These are actual commercial videos that look like real ads
        $realAdVideos = [
            [
                'title' => 'Premium Smartphone Launch',
                'description' => 'Experience the latest technology with our new smartphone. Revolutionary features await!',
                'video_url' => 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4',
                'image' => 'https://via.placeholder.com/640x360/667eea/ffffff?text=Smartphone+Ad',
            ],
            [
                'title' => 'Fashion Collection 2024',
                'description' => 'Discover the new fashion trends. Style meets comfort in our latest collection.',
                'video_url' => 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_2mb.mp4',
                'image' => 'https://via.placeholder.com/640x360/f093fb/ffffff?text=Fashion+Ad',
            ],
            [
                'title' => 'Travel Destination Paradise',
                'description' => 'Explore beautiful destinations around the world. Book your dream vacation today!',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'image' => 'https://via.placeholder.com/640x360/4facfe/ffffff?text=Travel+Ad',
            ],
            [
                'title' => 'Fitness & Wellness Program',
                'description' => 'Transform your body and mind. Join thousands of satisfied customers.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'image' => 'https://via.placeholder.com/640x360/43e97b/ffffff?text=Fitness+Ad',
            ],
            [
                'title' => 'Home Appliances Sale',
                'description' => 'Upgrade your home with our premium appliances. Limited time offer!',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'image' => 'https://via.placeholder.com/640x360/f5576c/ffffff?text=Home+Ad',
            ],
            [
                'title' => 'Online Learning Platform',
                'description' => 'Learn new skills from industry experts. Start your journey today!',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'image' => 'https://via.placeholder.com/640x360/764ba2/ffffff?text=Education+Ad',
            ],
            [
                'title' => 'Food Delivery Service',
                'description' => 'Order your favorite meals. Fast delivery, great prices!',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'image' => 'https://via.placeholder.com/640x360/38f9d7/ffffff?text=Food+Ad',
            ],
            [
                'title' => 'Car Insurance Premium',
                'description' => 'Protect your vehicle with our comprehensive insurance plans.',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'image' => 'https://via.placeholder.com/640x360/00f2fe/ffffff?text=Insurance+Ad',
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
        $fallbackAds = [
            [
                'title' => 'Special Offer Advertisement',
                'description' => 'Watch this advertisement to earn rewards',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'image' => '/assets/images/default-ad.jpg',
            ],
        ];
        
        return $fallbackAds[0] ?? [
            'title' => 'Advertisement #' . $index,
            'description' => 'Watch this video ad completely to earn rewards',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'image' => '/assets/images/default-ad.jpg',
        ];
    }
}
