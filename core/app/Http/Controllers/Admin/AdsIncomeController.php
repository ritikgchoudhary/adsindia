<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdPackageOrder;
use App\Models\AdView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdsIncomeController extends Controller
{
    public function settings()
    {
        $general = gs();
        $bullets = [];
        try {
            $raw = $general->ad_plans_info_bullets ?? null;
            if ($raw) {
                $decoded = json_decode((string) $raw, true);
                if (is_array($decoded)) $bullets = array_values(array_filter($decoded, fn($v) => is_string($v) && trim($v) !== ''));
            }
        } catch (\Throwable $e) {}

        $newUserRewards = [];
        try {
            $rawRewards = $general->new_user_offer_rewards ?? null;
            if ($rawRewards) {
                $newUserRewards = json_decode((string) $rawRewards, true) ?: [];
            }
        } catch (\Throwable $e) {}

        return responseSuccess('ads_income_settings', ['Ads income settings retrieved'], [
            'ads_enabled' => (int)($general->ads_enabled ?? 1),
            'ads_reward_mode' => (string)($general->ads_reward_mode ?? 'random'),
            'ads_reward_fixed' => (float)($general->ads_reward_fixed ?? 0),
            'ads_reward_min' => (float)($general->ads_reward_min ?? 25),
            'ads_reward_max' => (float)($general->ads_reward_max ?? 70),
            'ads_reward_step' => (float)($general->ads_reward_step ?? 1),
            'ads_reward_multiplier' => (float)($general->ads_reward_multiplier ?? 1),
            'new_user_offer_enabled' => (int)($general->new_user_offer_enabled ?? 1),
            'new_user_offer_ads' => (int)($general->new_user_offer_ads ?? 2),
            'new_user_offer_reward' => (float)($general->new_user_offer_reward ?? 5000),
            'new_user_offer_rewards' => $newUserRewards,
            // Ad Plans UI Text
            'ad_plans_info_title' => (string)($general->ad_plans_info_title ?? ''),
            'ad_plans_info_bullets' => $bullets,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'ads_enabled' => 'required|in:0,1',
            'ads_reward_mode' => 'required|in:random,fixed',
            'ads_reward_fixed' => 'nullable|numeric|min:0',
            'ads_reward_min' => 'nullable|numeric|min:0',
            'ads_reward_max' => 'nullable|numeric|min:0',
            'ads_reward_step' => 'nullable|numeric|min:0',
            'ads_reward_multiplier' => 'nullable|numeric|min:0',
            'new_user_offer_enabled' => 'required|in:0,1',
            'new_user_offer_ads' => 'nullable|integer|min:0|max:1000',
            'new_user_offer_reward' => 'nullable|numeric|min:0',
            'new_user_offer_rewards' => 'nullable|array',
            'ad_plans_info_title' => 'nullable|string|max:255',
            'ad_plans_info_bullets' => 'nullable|array',
            'ad_plans_info_bullets.*' => 'nullable|string|max:500',
        ]);

        $general = gs();
        $general->ads_enabled = (int)$request->ads_enabled;
        $general->ads_reward_mode = (string)$request->ads_reward_mode;
        $general->ads_reward_fixed = (float)($request->ads_reward_fixed ?? 0);
        $general->ads_reward_min = (float)($request->ads_reward_min ?? 1000);
        $general->ads_reward_max = (float)($request->ads_reward_max ?? 5000);
        $general->ads_reward_step = (float)($request->ads_reward_step ?? 100);
        $general->ads_reward_multiplier = (float)($request->ads_reward_multiplier ?? 1);
        $general->new_user_offer_enabled = (int)$request->new_user_offer_enabled;
        $general->new_user_offer_ads = (int)($request->new_user_offer_ads ?? 2);
        $general->new_user_offer_reward = (float)($request->new_user_offer_reward ?? 5000);
        $general->new_user_offer_rewards = json_encode($request->new_user_offer_rewards ?: []);

        // Ad Plans UI text
        $general->ad_plans_info_title = (string)($request->ad_plans_info_title ?? '');
        $bullets = $request->ad_plans_info_bullets;
        if (is_array($bullets)) {
            $clean = [];
            foreach ($bullets as $b) {
                $s = is_string($b) ? trim($b) : '';
                if ($s !== '') $clean[] = $s;
            }
            $general->ad_plans_info_bullets = json_encode(array_values($clean));
        } else {
            $general->ad_plans_info_bullets = null;
        }
        $general->save();

        Cache::forget('GeneralSetting');

        return responseSuccess('ads_income_settings_updated', ['Ads income settings updated successfully']);
    }

    public function liability(Request $request)
    {
        $general = gs();
        $mode = (string)($general->ads_reward_mode ?? 'random');
        $mult = (float)($general->ads_reward_multiplier ?? 1);
        $min = (float)($general->ads_reward_min ?? 1000);
        $max = (float)($general->ads_reward_max ?? 5000);
        $fixed = (float)($general->ads_reward_fixed ?? 0);

        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        $maxPerAd = ($mode === 'fixed' ? $fixed : $max) * $mult;
        $avgPerAd = ($mode === 'fixed' ? $fixed : (($min + $max) / 2)) * $mult;

        $today = Carbon::today();
        $activeOrders = AdPackageOrder::query()
            ->active()
            ->with('package:id,daily_ad_limit')
            ->get(['id', 'package_id']);

        $orderIds = $activeOrders->pluck('id')->all();
        $watchedCounts = [];
        if (!empty($orderIds)) {
            $watchedCounts = AdView::query()
                ->select('user_package_id', DB::raw('COUNT(*) as cnt'))
                ->whereIn('user_package_id', $orderIds)
                ->whereDate('viewed_at', $today)
                ->where('is_completed', true)
                ->groupBy('user_package_id')
                ->pluck('cnt', 'user_package_id')
                ->toArray();
        }

        $totalRemainingAds = 0;
        foreach ($activeOrders as $o) {
            $daily = (int)($o->package->daily_ad_limit ?? 0);
            $watched = (int)($watchedCounts[$o->id] ?? 0);
            $remaining = max(0, $daily - $watched);
            $totalRemainingAds += $remaining;
        }

        return responseSuccess('ads_liability', ['Ads liability calculated'], [
            'date' => $today->toDateString(),
            'active_orders' => (int)$activeOrders->count(),
            'total_remaining_ads' => (int)$totalRemainingAds,
            'max_reward_per_ad' => (float)$maxPerAd,
            'avg_reward_per_ad' => (float)$avgPerAd,
            'max_liability' => (float)($totalRemainingAds * $maxPerAd),
            'expected_liability' => (float)($totalRemainingAds * $avgPerAd),
        ]);
    }
}

