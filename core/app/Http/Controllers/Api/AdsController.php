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
    private function adsSettings(): array
    {
        $g = gs();
        $mode = (string) ($g->ads_reward_mode ?? 'random'); // random|fixed
        $mult = (float) ($g->ads_reward_multiplier ?? 1);
        $min = (float) ($g->ads_reward_min ?? 25);
        $max = (float) ($g->ads_reward_max ?? 70);
        $fixed = (float) ($g->ads_reward_fixed ?? 0);
        $step = (float) ($g->ads_reward_step ?? 1);
        if ($min > $max) [$min, $max] = [$max, $min];
        if ($step <= 0) $step = 1;
        if ($mult <= 0) $mult = 1;

        $newUserRewards = [];
        try {
            $rawRewards = $g->new_user_offer_rewards ?? null;
            if ($rawRewards) {
                $newUserRewards = json_decode((string) $rawRewards, true) ?: [];
            }
        } catch (\Throwable $e) {}

        return [
            'enabled' => (int) ($g->ads_enabled ?? 1) === 1,
            'mode' => in_array($mode, ['random', 'fixed'], true) ? $mode : 'random',
            'mult' => $mult,
            'min' => $min,
            'max' => $max,
            'fixed' => $fixed,
            'step' => $step,
            'new_user_offer_enabled' => (int) ($g->new_user_offer_enabled ?? 1) === 1,
            'new_user_offer_ads' => max(0, (int) ($g->new_user_offer_ads ?? 2)),
            'new_user_offer_reward' => (float) ($g->new_user_offer_reward ?? 5000),
            'new_user_offer_rewards' => $newUserRewards,
        ];
    }

    private function computeEarning(array $settings, string $seedKey, ?float $packageMax = null): float
    {
        $mode = $settings['mode'] ?? 'random';
        $mult = (float) ($settings['mult'] ?? 1);

        if ($mode === 'fixed') {
            $base = (float) ($settings['fixed'] ?? 0);
            return round($base * $mult, 2);
        }

        $min = (float) ($settings['min'] ?? 0);
        $max = (float) ($packageMax ?: ($settings['max'] ?? 0));
        $step = (float) ($settings['step'] ?? 1);
        
        if ($min > $max) [$min, $max] = [$max, $min];
        if ($step <= 0) $step = 1;

        $span = max(0.0, $max - $min);
        $steps = (int) floor($span / $step);
        $n = max(1, $steps + 1);

        $idx = abs((int) crc32($seedKey)) % $n;
        $val = $min + ($idx * $step);
        if ($val > $max) $val = $max;
        return round($val * $mult, 2);
    }

    /**
     * New user = never purchased any ad package. New user gets 2 ads only, 5000 each = 10K total (one-time offer).
     */
    private function isNewUserEligibleForOffer($user)
    {
        $s = $this->adsSettings();
        if (!($s['new_user_offer_enabled'] ?? true)) return false;
        $limit = (int) ($s['new_user_offer_ads'] ?? 2);
        if ($limit <= 0) return false;
        $hasAnyPackage = AdPackageOrder::where('user_id', $user->id)->exists();
        $watched = (int) ($user->new_user_ads_watched ?? 0);
        return !$hasAnyPackage && $watched < $limit;
    }

    public function getAds()
    {
        $user = auth()->user();
        $general = gs();
        $settings = $this->adsSettings();

        if (!($settings['enabled'] ?? true)) {
            return responseError('ads_disabled', ['Ads earning is temporarily disabled. Please try again later.']);
        }

        // Get active ad package order
        $activeOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->orderByDesc('id')
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

        // Next ad must be watched in sequence (1 -> 2 -> 3 ...)
        $expectedAdId = 1;
        while (in_array($expectedAdId, $watchedAdIds, true)) {
            $expectedAdId++;
        }

        // Check if daily limit reached
        $dailyLimit = (int) ($activeOrder->package->daily_ad_limit ?? 0);
        $canWatchMore = $todayViews < $dailyLimit;
        $remainingAds = $dailyLimit - $todayViews;
        $realAds = $this->fetchRealAdsFromAPI($dailyLimit);
        $ads = [];

        for ($i = 1; $i <= $dailyLimit; $i++) {
            $seed = $user->id . '|' . $activeOrder->id . '|' . $i . '|' . today()->toDateString();
            $packageRewardMax = (float)($activeOrder->package->reward_per_ad ?? 0);
            $earning = $this->computeEarning($settings, $seed, $packageRewardMax ?: null);
            $adData = isset($realAds[$i - 1]) ? $realAds[$i - 1] : $this->getFallbackAd($i);
            $isWatched = in_array($i, $watchedAdIds);
            $isUnlocked = $i <= $expectedAdId;
            $ads[] = [
                'id' => $i,
                'title' => $adData['title'],
                'description' => $adData['description'] ?? 'Watch this video ad completely to earn ' . showAmount($earning) . '. Video duration: ' . ($activeOrder->package->duration_seconds / 60) . ' minutes.',
                'video_url' => $adData['video_url'],
                'image' => $adData['image'] ?? '/assets/images/default-ad.jpg',
                'duration' => (int)($activeOrder->package->duration_seconds / 60),
                'duration_seconds' => $activeOrder->package->duration_seconds,
                'earning' => (float)$earning,
                'earning_min' => (float) ($settings['mode'] === 'fixed' ? ($settings['fixed'] * $settings['mult']) : ($settings['min'] * $settings['mult'])),
                'earning_max' => (float) ($packageRewardMax ?: ($settings['mode'] === 'fixed' ? ($settings['fixed'] * $settings['mult']) : ($settings['max'] * $settings['mult']))),
                // Only next expected ad can be watched (sequence-based) and only if daily limit not reached
                'is_active' => $canWatchMore && !$isWatched && $i === $expectedAdId,
                'is_watched' => $isWatched,
                'is_unlocked' => $isUnlocked,
                'timer' => null,
            ];
        }

        return responseSuccess('ads_data', ['Ads retrieved successfully'], [
            'data' => $ads,
            'active_package' => [
                'package_id' => (int) $activeOrder->package->id,
                'order_id' => (int) $activeOrder->id,
                'name' => $activeOrder->package->name,
                'daily_limit' => $dailyLimit,
                'duration_seconds' => (int) ($activeOrder->package->duration_seconds ?? 60),
                'expires_at' => $activeOrder->expires_at ? $activeOrder->expires_at->toDateTimeString() : null,
                'today_views' => $todayViews,
                'remaining_ads' => max(0, $remainingAds),
                'next_ad_id' => $canWatchMore ? $expectedAdId : null,
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
        $settings = $this->adsSettings();
        $watched = (int) ($user->new_user_ads_watched ?? 0);
        $expectedAdId = $watched + 1;
        $limit = max(0, (int) ($settings['new_user_offer_ads'] ?? 2));
        $durationSeconds = 30 * 60; // 30 minutes per ad for new users
        $newUserRewards = $settings['new_user_offer_rewards'] ?? [];

        for ($i = 1; $i <= $limit; $i++) {
            $isWatched = $watched >= $i;
            $adData = $realAds[$i - 1] ?? $this->getFallbackAd($i);
            
            // Use specific reward if set, else fallback to global new user reward
            $earningPerAd = (float)($newUserRewards[$i - 1] ?? ($settings['new_user_offer_reward'] ?? 5000));
            $earningPerAd = round($earningPerAd * (float) ($settings['mult'] ?? 1), 2);

            $ads[] = [
                'id' => $i,
                'title' => $adData['title'],
                'description' => 'New user offer: Watch this ad completely (30 minutes) to earn ' . showAmount($earningPerAd) . '.',
                'video_url' => $adData['video_url'],
                'image' => $adData['image'] ?? '/assets/images/default-ad.jpg',
                'duration' => (int) ceil($durationSeconds / 60),
                'duration_seconds' => $durationSeconds,
                'earning' => (float) $earningPerAd,
                // Sequence-based: must watch 1 then 2
                'is_active' => !$isWatched && $i === $expectedAdId,
                'is_watched' => $isWatched,
                'is_unlocked' => $i <= $expectedAdId,
                'timer' => null,
            ];
        }

        $total = round($earningPerAd * max(1, $limit), 2);
        return responseSuccess('ads_data', ['New user offer: Watch ' . $limit . ' ads to earn ' . showAmount($total)], [
            'data' => $ads,
            'active_package' => [
                'package_id' => 0,
                'order_id' => 0,
                'name' => 'New User Offer',
                'daily_limit' => $limit,
                'duration_seconds' => $durationSeconds,
                'today_views' => $watched,
                'remaining_ads' => max(0, $limit - $watched),
                'next_ad_id' => $expectedAdId <= $limit ? $expectedAdId : null,
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
            ->orderByDesc('id')
            ->first();

        // New user offer: 2 ads, 5000 each (no package required)
        if (!$activeOrder) {
            $settings = $this->adsSettings();
            $limit = max(0, (int) ($settings['new_user_offer_ads'] ?? 2));
            if ($limit > 0 && $this->isNewUserEligibleForOffer($user) && (int) $request->ad_id >= 1 && (int) $request->ad_id <= $limit) {
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
            ->orderBy('viewed_at', 'asc')
            ->get();
        
        // Check if this ad_id was already watched
        foreach ($watchedAdViews as $view) {
            if (preg_match('/ad_id:(\d+)/', $view->ad_url, $matches)) {
                if ((int)$matches[1] === (int)$request->ad_id) {
                    return responseError('already_watched', ['This ad has already been watched today']);
                }
            }
        }

        // Enforce sequential watching (must watch ad 1 -> 2 -> 3 ...)
        $watchedAdIds = [];
        $viewIndex = 0;
        foreach ($watchedAdViews as $view) {
            if (preg_match('/ad_id:(\d+)/', $view->ad_url, $matches)) {
                $watchedAdIds[] = (int) $matches[1];
            } else {
                $watchedAdIds[] = $viewIndex + 1;
            }
            $viewIndex++;
        }
        $expectedAdId = 1;
        while (in_array($expectedAdId, $watchedAdIds, true)) {
            $expectedAdId++;
        }
        if ((int) $request->ad_id !== (int) $expectedAdId) {
            return responseError('sequential_required', ['Please watch ad ' . $expectedAdId . ' next.']);
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

        $settings = $this->adsSettings();
        if (!($settings['enabled'] ?? true)) {
            return responseError('ads_disabled', ['Ads earning is temporarily disabled. Please try again later.']);
        }
        $seed = $user->id . '|' . $activeOrder->id . '|' . (int)$request->ad_id . '|' . today()->toDateString();
        $packageRewardMax = (float)($activeOrder->package->reward_per_ad ?? 0);
        $earning = $this->computeEarning($settings, $seed, $packageRewardMax ?: null);

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
        $settings = $this->adsSettings();
        $watched = (int) ($user->new_user_ads_watched ?? 0);
        $expectedAdId = $watched + 1;
        if ((int)$request->ad_id !== $expectedAdId) {
            return responseError('invalid_ad_order', ['Please watch ad ' . $expectedAdId . ' next.']);
        }

        $durationSeconds = 30 * 60; // 30 minutes per ad for new users
        $minWatchDuration = (int) ($durationSeconds * 0.9);
        if ($request->watch_duration < $minWatchDuration) {
            return responseError('incomplete_watch', ['Please watch the complete video to earn reward']);
        }

        $newUserRewards = $settings['new_user_offer_rewards'] ?? [];
        $earning = (float)($newUserRewards[$expectedAdId - 1] ?? ($settings['new_user_offer_reward'] ?? 5000));
        $earning = round($earning * (float) ($settings['mult'] ?? 1), 2);

        $user->balance = ($user->balance ?? 0) + $earning;
        $user->new_user_ads_watched = $expectedAdId;
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

        $limit = max(0, (int) ($settings['new_user_offer_ads'] ?? 2));
        $total = round($earning * max(1, $limit), 2);
        $done = (int) ($user->new_user_ads_watched ?? 0) >= $limit;
        return responseSuccess('ad_completed', ['Ad watched! You earned ' . showAmount($earning) . '. ' . ($done ? ('Complete KYC and withdraw your ' . showAmount($total) . '.') : ('Watch the next ad to complete ' . showAmount($total) . '.'))], [
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
        // IMPORTANT:
        // Some public sample buckets (e.g. commondatastorage.googleapis.com) may return 403 on mobile networks/devices.
        // Use multiple reliable sources that allow direct MP4 playback.
        $realAdVideos = [
            [
                'title' => 'Test Ad Video 1',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'image' => 'https://picsum.photos/640/360?random=1',
            ],
            [
                'title' => 'Test Ad Video 2',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
                'image' => 'https://picsum.photos/640/360?random=2',
            ],
            [
                'title' => 'Test Ad Video 3',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://filesamples.com/samples/video/mp4/sample_640x360.mp4',
                'image' => 'https://picsum.photos/640/360?random=3',
            ],
            [
                'title' => 'Test Ad Video 4',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'image' => 'https://picsum.photos/640/360?random=4',
            ],
            [
                'title' => 'Test Ad Video 5',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
                'image' => 'https://picsum.photos/640/360?random=5',
            ],
            [
                'title' => 'Test Ad Video 6',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://filesamples.com/samples/video/mp4/sample_640x360.mp4',
                'image' => 'https://picsum.photos/640/360?random=6',
            ],
            [
                'title' => 'Test Ad Video 7',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'image' => 'https://picsum.photos/640/360?random=7',
            ],
            [
                'title' => 'Test Ad Video 8',
                'description' => 'Watch this test video to earn rewards.',
                'video_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
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
        // Fallback test videos from reliable sources
        $fallbackAds = [
            [
                'title' => 'Test Ad Video',
                'description' => 'Watch this test video to earn rewards',
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'image' => '/assets/images/default-ad.jpg',
            ],
        ];
        
        return $fallbackAds[0] ?? [
            'title' => 'Test Ad Video #' . $index,
            'description' => 'Watch this test video to earn rewards',
            'video_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
            'image' => '/assets/images/default-ad.jpg',
        ];
    }
}
