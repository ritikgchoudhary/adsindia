<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdPackageOrder;
use App\Models\AdView;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdViewController extends Controller {
    public function index() {
        $pageTitle = 'View Ad & Earn';
        $user = auth()->user();

        // Get active ad package order
        $activeOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->first();

        if (!$activeOrder) {
            $notify[] = ['error', 'You need to purchase an ad package first'];
            // Redirect to dashboard if ad packages route doesn't exist
            return redirect()->route('user.home')->withNotify($notify);
        }

        // Get today's ad views count
        $todayViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->count();

        // Check if daily limit reached
        if ($todayViews >= $activeOrder->package->daily_ad_limit) {
            $notify[] = ['info', 'You have reached your daily ad viewing limit'];
        }

        return view('Template::user.ad_view.index', compact('pageTitle', 'activeOrder', 'todayViews'));
    }

    public function getAvailableAds() {
        $user = auth()->user();

        $activeOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->first();

        if (!$activeOrder) {
            return response()->json([
                'success' => false,
                'message' => 'No active ad package found'
            ]);
        }

        // Get today's ad views count
        $todayViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->count();

        if ($todayViews >= $activeOrder->package->daily_ad_limit) {
            return response()->json([
                'success' => false,
                'message' => 'Daily ad viewing limit reached'
            ]);
        }

        // Fetch random videos from Google Cloud Storage API
        $videos = $this->fetchRandomVideos();

        return response()->json([
            'success' => true,
            'videos' => $videos,
            'duration' => $activeOrder->package->duration_seconds,
            'reward' => $activeOrder->package->reward_per_ad,
            'remaining_ads' => $activeOrder->package->daily_ad_limit - $todayViews
        ]);
    }

    private function fetchRandomVideos() {
        try {
            // Google Cloud Storage sample videos API
            $url = 'https://storage.googleapis.com/gtv-videos-bucket/sample/videos.json';
            $response = @file_get_contents($url);

            if ($response === false) {
                // Fallback to hardcoded videos
                return $this->getFallbackVideos();
            }

            $data = json_decode($response, true);
            
            if (isset($data['categories']) && count($data['categories']) > 0) {
                $videos = [];
                foreach ($data['categories'] as $category) {
                    if (isset($category['videos']) && is_array($category['videos'])) {
                        $videos = array_merge($videos, $category['videos']);
                    }
                }
                
                if (count($videos) > 0) {
                    // Return random video
                    $randomVideo = $videos[array_rand($videos)];
                    return [[
                        'sources' => $randomVideo['sources'] ?? [],
                        'thumb' => $randomVideo['thumb'] ?? '',
                        'title' => $randomVideo['title'] ?? 'Sample Video'
                    ]];
                }
            }

            return $this->getFallbackVideos();
        } catch (\Exception $e) {
            Log::error('Error fetching videos: ' . $e->getMessage());
            return $this->getFallbackVideos();
        }
    }

    private function getFallbackVideos() {
        return [[
            'sources' => [
                'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4'
            ],
            'thumb' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/images/BigBuckBunny.jpg',
            'title' => 'Big Buck Bunny'
        ]];
    }

    public function completeAdView(Request $request) {
        $request->validate([
            'ad_url' => 'required|url',
            'watch_duration' => 'required|integer|min:0',
        ]);

        $user = auth()->user();

        $activeOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->first();

        if (!$activeOrder) {
            return response()->json([
                'success' => false,
                'message' => 'No active ad package found'
            ]);
        }

        // Check if video was watched at least 90% of required duration
        $requiredDuration = $activeOrder->package->duration_seconds;
        $minWatchDuration = (int)($requiredDuration * 0.9); // 90% of required duration

        if ($request->watch_duration < $minWatchDuration) {
            return response()->json([
                'success' => false,
                'message' => 'Please watch the complete video to earn reward'
            ]);
        }

        // Check daily limit
        $todayViews = AdView::where('user_id', $user->id)
            ->where('user_package_id', $activeOrder->id)
            ->whereDate('viewed_at', today())
            ->count();

        if ($todayViews >= $activeOrder->package->daily_ad_limit) {
            return response()->json([
                'success' => false,
                'message' => 'Daily ad viewing limit reached'
            ]);
        }

        // Create ad view record
        $adView = AdView::create([
            'user_id' => $user->id,
            'user_package_id' => $activeOrder->id,
            'ad_url' => $request->ad_url,
            'reward_amount' => $activeOrder->package->reward_per_ad,
            'watch_duration' => $request->watch_duration,
            'is_completed' => true,
            'viewed_at' => now(),
        ]);

        // Add reward to user balance
        $user->balance += $activeOrder->package->reward_per_ad;
        $user->save();

        // Create transaction record
        Transaction::create([
            'user_id' => $user->id,
            'amount' => $activeOrder->package->reward_per_ad,
            'post_balance' => $user->balance,
            'charge' => 0,
            'trx_type' => '+',
            'trx' => getTrx(),
            'remark' => 'ad_view_reward',
            'details' => 'Reward for viewing ad',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reward added successfully',
            'reward' => $activeOrder->package->reward_per_ad,
            'balance' => $user->balance,
            'remaining_ads' => $activeOrder->package->daily_ad_limit - ($todayViews + 1)
        ]);
    }

    public function history() {
        $pageTitle = 'View Ad History';
        $user = auth()->user();

        $adViews = AdView::where('user_id', $user->id)
            ->with('adPackageOrder.package')
            ->latest('viewed_at')
            ->paginate(getPaginate());

        return view('Template::user.ad_view.history', compact('pageTitle', 'adViews'));
    }
}
