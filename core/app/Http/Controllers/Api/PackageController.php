<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdPackage;
use App\Models\AdPackageOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function getPackages()
    {
        $general = gs();

        // Define 5 specific packages as per requirements
        $packagesData = [
            [
                'id' => 1,
                'name' => 'AdsLite',
                'price' => 1499,
                'validity_days' => 30,
                'daily_ads_limit' => 2,
                'earning_per_ad' => 5000,
                'max_daily_earning' => 10000,
                'features' => ['2 Ads per day', 'Earn ₹5,000 per ad', 'Basic Support'],
                'is_recommended' => false,
            ],
            [
                'id' => 2,
                'name' => 'AdsPro',
                'price' => 2999,
                'validity_days' => 30,
                'daily_ads_limit' => 5,
                'earning_per_ad' => 5500,
                'max_daily_earning' => 27500,
                'features' => ['5 Ads per day', 'Earn ₹5,500 per ad', 'Priority Support'],
                'is_recommended' => true,
            ],
            [
                'id' => 3,
                'name' => 'AdsSupreme',
                'price' => 5999,
                'validity_days' => 30,
                'daily_ads_limit' => 10,
                'earning_per_ad' => 6000,
                'max_daily_earning' => 60000,
                'features' => ['10 Ads per day', 'Earn ₹6,000 per ad', 'VIP Support'],
                'is_recommended' => false,
            ],
            [
                'id' => 4,
                'name' => 'AdsPremium',
                'price' => 9999,
                'validity_days' => 30,
                'daily_ads_limit' => 20,
                'earning_per_ad' => 6000,
                'max_daily_earning' => 120000,
                'features' => ['20 Ads per day', 'Earn ₹6,000 per ad', 'Premium Support'],
                'is_recommended' => false,
            ],
            [
                'id' => 5,
                'name' => 'AdsPremium+',
                'price' => 15999,
                'validity_days' => 30,
                'daily_ads_limit' => 35,
                'earning_per_ad' => 6000,
                'max_daily_earning' => 210000,
                'features' => ['35 Ads per day', 'Earn ₹6,000 per ad', 'Premium+ Support', 'All Benefits'],
                'is_recommended' => false,
            ],
        ];

        return responseSuccess('packages', ['Packages retrieved successfully'], [
            'data' => $packagesData,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function getCurrentPackage()
    {
        $user = auth()->user();
        $general = gs();

        $currentOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->with('package')
            ->latest()
            ->first();

        $currentPackage = null;
        if ($currentOrder && $currentOrder->package) {
            $pkg = $currentOrder->package;
            $maxDailyEarning = $pkg->daily_ad_limit * $pkg->reward_per_ad;
            
            $currentPackage = [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'price' => (float)$pkg->price,
                'valid_until' => $currentOrder->expires_at ? $currentOrder->expires_at->format('Y-m-d H:i:s') : null,
                'daily_ads_limit' => $pkg->daily_ad_limit,
                'earning_per_ad' => (float)$pkg->reward_per_ad,
                'max_daily_earning' => (float)$maxDailyEarning,
                'features' => [
                    $pkg->daily_ad_limit . ' Ads per day',
                    'Earn ' . showAmount($pkg->reward_per_ad) . ' per ad',
                ],
            ];
        }

        return responseSuccess('current_package', ['Current package retrieved successfully'], [
            'data' => $currentPackage,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function purchasePackage(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'package_id' => 'required|integer|in:1,2,3,4,5',
        ]);

        $packagesData = [
            1 => ['name' => 'AdsLite', 'price' => 1499],
            2 => ['name' => 'AdsPro', 'price' => 2999],
            3 => ['name' => 'AdsSupreme', 'price' => 5999],
            4 => ['name' => 'AdsPremium', 'price' => 9999],
            5 => ['name' => 'AdsPremium+', 'price' => 15999],
        ];

        $packageId = $request->package_id;
        $package = $packagesData[$packageId] ?? null;

        if (!$package) {
            return responseError('package_not_found', ['Package not found']);
        }

        // Get current package
        $currentOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->latest()
            ->first();

        $remainingAmount = 0;
        if ($currentOrder) {
            // Calculate remaining amount if upgrading
            $currentPackagePrice = 0;
            foreach ($packagesData as $id => $pkg) {
                if ($id == $currentOrder->package_id) {
                    $currentPackagePrice = $pkg['price'];
                    break;
                }
            }
            if ($package['price'] > $currentPackagePrice) {
                $remainingAmount = $package['price'] - $currentPackagePrice;
            }
        } else {
            $remainingAmount = $package['price'];
        }

        if ($remainingAmount > 0 && $user->balance < $remainingAmount) {
            return responseError('insufficient_balance', ['Insufficient balance. You need to pay ₹' . number_format($remainingAmount, 2) . ' more']);
        }

        // Deduct remaining amount
        if ($remainingAmount > 0) {
            $user->balance -= $remainingAmount;
            $user->save();

            // Create transaction
            $trx = getTrx();
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $remainingAmount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '-';
            $transaction->details = 'Upgrade package: ' . $package['name'];
            $transaction->trx = $trx;
            $transaction->remark = 'package_upgrade';
            $transaction->save();
        }

        // Create or update ad package order
        if ($currentOrder) {
            $currentOrder->package_id = $packageId;
            $currentOrder->amount = $package['price'];
            $currentOrder->expires_at = now()->addDays(30);
            $currentOrder->save();
            $order = $currentOrder;
        } else {
            $order = AdPackageOrder::create([
                'user_id' => $user->id,
                'package_id' => $packageId,
                'amount' => $package['price'],
                'status' => 1,
                'expires_at' => now()->addDays(30),
            ]);
        }

        return responseSuccess('package_purchased', ['Package upgraded successfully'], [
            'order_id' => $order->id,
            'expires_at' => $order->expires_at,
            'remaining_amount_paid' => $remainingAmount,
        ]);
    }
}
