<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpecialDiscountLink;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ReferralController extends Controller
{
    private static function hmacKey(): string
    {
        $key = (string) config('app.key', '');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }
        return $key;
    }

    /**
     * Sign a referral link to prevent tampering.
     * Backward compatible: if discount is null, signature uses ref|pkg (legacy).
     * If discount is provided, signature uses ref|pkg|discount.
     */
    private static function packageSig(string $refCode, int $pkgId, ?int $discount = null): string
    {
        $payload = strtolower(trim($refCode)) . '|' . (int) $pkgId;
        if ($discount !== null) {
            $payload .= '|' . (int) $discount;
        }
        return hash_hmac('sha256', $payload, static::hmacKey());
    }

    private static function packagesData(): array
    {
        return [
            1 => ['name' => 'AdsLite',     'price' => 1499],
            2 => ['name' => 'AdsPro',      'price' => 2999],
            3 => ['name' => 'AdsSupreme',  'price' => 5999],
            4 => ['name' => 'AdsPremium',  'price' => 9999],
            5 => ['name' => 'AdsPremium+', 'price' => 15999],
        ];
    }

    /**
     * Generate a special referral link with a chosen package and discount.
     * The discount is validated and signed.
     */
    public function generateSpecialLink(Request $request)
    {
        $request->validate([
            'package_id' => 'required|integer|in:1,2,3,4,5',
            'discount' => 'required|integer|min:0',
        ]);

        $user = auth()->user();
        $refCode = 'ADS' . $user->id;

        $pkgId = (int) $request->package_id;
        $discount = (int) $request->discount;

        $packages = static::packagesData();
        $pkg = $packages[$pkgId] ?? null;
        if (!$pkg) {
            return responseError('invalid_package', ['Invalid package']);
        }

        $price = (int) ($pkg['price'] ?? 0);
        if ($discount > $price) {
            return responseError('invalid_discount', ['Discount cannot be greater than package price']);
        }

        // If discount is 0, omit the discount param and use legacy signature
        if ($discount === 0) {
            $sig = static::packageSig($refCode, $pkgId, null);
            $link = URL::to('/register?ref=' . $refCode . '&pkg=' . $pkgId . '&sig=' . $sig);
        } else {
            $sig = static::packageSig($refCode, $pkgId, $discount);
            $link = URL::to('/register?ref=' . $refCode . '&pkg=' . $pkgId . '&disc=' . $discount . '&sig=' . $sig);
        }

        return responseSuccess('special_link', ['Special link generated'], [
            'package_id' => $pkgId,
            'package_name' => (string) ($pkg['name'] ?? 'Package'),
            'original_price' => (int) $price,
            'discount' => (int) $discount,
            'final_price' => (int) ($price - $discount),
            'link' => $link,
        ]);
    }

    public function getReferralData()
    {
        $user = auth()->user();
        $general = gs();

        // Generate package-specific referral links
        $packages = [
            ['id' => 1, 'name' => 'AdsLite', 'price' => 1499, 'discount' => 0],
            ['id' => 2, 'name' => 'AdsPro', 'price' => 2999, 'discount' => 0],
            ['id' => 3, 'name' => 'AdsSupreme', 'price' => 5999, 'discount' => 0],
            ['id' => 4, 'name' => 'AdsPremium', 'price' => 9999, 'discount' => 0],
            ['id' => 5, 'name' => 'AdsPremium+', 'price' => 15999, 'discount' => 0],
        ];

        // Ref code format: virtualized ID
        $refCode = $user->display_id;

        $referralLinks = [];
        foreach ($packages as $pkg) {
            $discountedPrice = $pkg['price'] - $pkg['discount'];
            $sig = static::packageSig($refCode, (int) $pkg['id'], null);
            $referralLinks[] = [
                'package_id' => $pkg['id'],
                'package_name' => $pkg['name'],
                'original_price' => $pkg['price'],
                'discount' => $pkg['discount'],
                'discounted_price' => $discountedPrice,
                'link' => URL::to('/register?ref=' . $refCode . '&pkg=' . $pkg['id'] . '&sig=' . $sig),
            ];
        }

        // General referral link (ref = ADS + user id)
        $referralLink = URL::to('/register?ref=' . $refCode);

        // Get downline team
        $downlineTeam = User::where('ref_by', $user->id)
            ->latest()
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'display_id' => $member->display_id,
                    'firstname' => (string) ($member->firstname ?? ''),
                    'lastname' => (string) ($member->lastname ?? ''),
                    'username' => $member->username,
                    'email' => $member->email,
                    'joined_at' => $member->created_at->format('Y-m-d H:i:s'),
                    'status' => $member->status == 1 ? 'active' : 'inactive',
                    'earning' => Transaction::where('user_id', $member->id)
                        ->where('trx_type', '+')
                        ->sum('amount'),
                ];
            });

        $commissionRemarks = [
            'referral_commission',
            'downline_commission',
            'affiliate_commission',
            'direct_affiliate_commission',
            'agent_registration_commission',
            'agent_kyc_commission',
            'agent_withdraw_fee_commission',
            'agent_upgrade_commission',
            'agent_course_commission',
            'agent_adplan_commission',
            'agent_partner_commission',
            'agent_certificate_commission',
            'agent_partner_override_commission',
        ];

        $teamStats = [
            'total_members' => User::where('ref_by', $user->id)->count(),
            'active_members' => User::where('ref_by', $user->id)->where('status', 1)->count(),
            'total_earning' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $commissionRemarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->sum('amount'),
        ];

        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $referralEarning = [
            'today' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $commissionRemarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->where('created_at', '>=', $today)
                ->sum('amount'),
            'this_month' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $commissionRemarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount'),
            'total' => Transaction::where('user_id', $user->id)
                ->whereIn('remark', $commissionRemarks)
                ->where('wallet', 'affiliate')
                ->where('trx_type', '+')
                ->sum('amount'),
        ];

        $globalSpecialLinks = collect();
        try {
            $packagesMeta = static::packagesData();
            $globalSpecialLinks = SpecialDiscountLink::where('is_active', true)
                ->where('is_global', true)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(function (SpecialDiscountLink $row) use ($packagesMeta, $refCode) {
                    $pkgId = (int) $row->package_id;
                    $pkg = $packagesMeta[$pkgId] ?? null;
                    $price = (int) ($pkg['price'] ?? 0);
                    $discount = max(0, (int) $row->discount);
                    if ($discount > $price) {
                        $discount = $price;
                    }

                    // IMPORTANT: In user panel we show "shareable" special links that still credit the user.
                    // So we generate a signed link using the user's own ref code (ADS{userId}),
                    // while the allowed (pkg, discount) pairs come from the global saved list.
                    if ($discount === 0) {
                        $sig = static::packageSig($refCode, $pkgId, null);
                        $link = URL::to('/register?ref=' . $refCode . '&pkg=' . $pkgId . '&sig=' . $sig);
                    } else {
                        $sig = static::packageSig($refCode, $pkgId, $discount);
                        $link = URL::to('/register?ref=' . $refCode . '&pkg=' . $pkgId . '&disc=' . $discount . '&sig=' . $sig);
                    }

                    return [
                        'id' => (int) $row->id,
                        'package_id' => $pkgId,
                        'package_name' => (string) ($pkg['name'] ?? ('Plan ' . $pkgId)),
                        'original_price' => $price,
                        'discount' => $discount,
                        'final_price' => $price - $discount,
                        'link' => $link,
                        'created_at' => $row->created_at ? (string) $row->created_at : null,
                    ];
                })
                ->values();
        } catch (\Throwable $e) {
            $globalSpecialLinks = collect();
        }

        return responseSuccess('referral_data', ['Referral data retrieved successfully'], [
            'referral_code' => $refCode,
            'referral_link' => $referralLink,
            'package_links' => $referralLinks,
            'global_special_links' => $globalSpecialLinks,
            'downline_team' => $downlineTeam,
            'team_stats' => $teamStats,
            'referral_earning' => $referralEarning,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'is_partner' => (bool)($user->partner_plan_id && $user->partner_plan_valid_until && now()->lt($user->partner_plan_valid_until)),
            'is_agent' => (bool)$user->is_agent,
        ]);
    }
}
