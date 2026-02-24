<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\AgentCommission;
use App\Lib\DirectAffiliateCommission;
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
            'payment_method' => 'required|in:gateway',
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush,custom_qr',
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

        $gateway = $request->input('gateway', 'watchpay');
        if (!in_array($gateway, ['watchpay', 'simplypay', 'rupeerush', 'custom_qr'])) {
            $gateway = 'watchpay';
        }

        $gw = \App\Models\Gateway::where('alias', $gateway)->first();
        if (!$gw || $gw->status != 1) {
            return responseError('gateway_unavailable', ['Selected payment gateway is currently unavailable.']);
        }

        if ($gateway === 'custom_qr') {
            $qrImages = $gw->extra ?? [];
            if (empty($qrImages)) {
                return responseError('gateway_unavailable', ['Manual QR system is currently not available. Please contact admin.']);
            }
        }

        // Get current package
        $currentOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->latest()
            ->first();

        // Check if user can upgrade (only to next package, not skipping)
        $currentPackageId = $currentOrder ? $currentOrder->package_id : 0;
        
        // User can only upgrade to next package (sequential upgrade)
        if ($currentPackageId > 0 && $packageId <= $currentPackageId) {
            return responseError('invalid_upgrade', ['You can only upgrade to a higher package. Current package: ' . ($packagesData[$currentPackageId]['name'] ?? 'Unknown')]);
        }
        
        // Check if skipping packages (not allowed - must upgrade sequentially)
        if ($currentPackageId > 0 && $packageId > $currentPackageId + 1) {
            return responseError('sequential_upgrade_required', ['You can only upgrade to the next package. Please upgrade to ' . ($packagesData[$currentPackageId + 1]['name'] ?? 'next package') . ' first.']);
        }

        // First package purchase - must start with AdsLite (package 1)
        if (!$currentOrder && $packageId != 1) {
            return responseError('start_with_ads_lite', ['Please start with AdsLite package first.']);
        }

        $remainingAmount = 0;
        if ($currentOrder) {
            // Calculate remaining amount if upgrading
            $currentPackagePrice = $packagesData[$currentPackageId]['price'] ?? 0;
            if ($package['price'] > $currentPackagePrice) {
                $remainingAmount = $package['price'] - $currentPackagePrice;
            }
        } else {
            // First package purchase
            $remainingAmount = $package['price'];
        }

        // Only gateway payment is allowed
        return $this->initiateGatewayPayment($user, $packageId, $package, $remainingAmount, $currentOrder, $gateway);
    }

    /**
     * Process package purchase with balance
     */
    private function processPackagePurchase($user, $packageId, $package, $remainingAmount, $currentOrder, ?string $gatewayTrx = null, ?string $gatewayName = null)
    {
        $postBalance = (float) ($user->balance ?? 0);

        // Log transaction as paid via gateway (without changing balance)
        if ($remainingAmount > 0) {
            $trx = $gatewayTrx ?: getTrx();
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $remainingAmount;
            $transaction->post_balance = $postBalance;
            $transaction->charge = 0;
            $transaction->trx_type = '-';
            $transaction->details = 'Upgrade package via ' . ($gatewayName ?: 'Gateway') . ': ' . $package['name'];
            $transaction->trx = $trx;
            $transaction->remark = 'package_upgrade_gateway';
            $transaction->save();

            // Direct affiliate commission (ALL users) – package-wise fixed amount (Master Admin controlled)
            try {
                DirectAffiliateCommission::creditForPackage(
                    $user,
                    (int) $packageId,
                    (float) $remainingAmount,
                    (string) $trx,
                    (string) ($package['name'] ?? '')
                );
            } catch (\Throwable $e) {
                // non-blocking
            }

            // Agent commission (only if sponsor is Agent) – allow per-plan override by package id
            try {
                $agentId = (int) ($user->ref_by ?? 0);
                if ($agentId > 0) {
                    $commissionType = $currentOrder ? 'upgrade' : 'registration';
                    AgentCommission::process(
                        $agentId,
                        $commissionType,
                        (float) $remainingAmount,
                        (string) $trx,
                        'Agent commission from User#' . (int) $user->id . ' – Package: ' . (string) ($package['name'] ?? '') . ' | Base: ₹' . (float) $remainingAmount,
                        ['plan_type' => 'package', 'plan_id' => (int) $packageId]
                    );
                }
            } catch (\Throwable $e) {
                // non-blocking
            }
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

    /**
     * Initiate dummy gateway payment
     */
    private function initiateGatewayPayment($user, $packageId, $package, $remainingAmount, $currentOrder, string $gateway = 'watchpay')
    {
        $trx = getTrx();

        // Create payment session for gateway IPN to update
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        if ($gateway === 'custom_qr') $cachePrefix = 'custom_qr_payment_';
        
        $cacheKey = $cachePrefix . $trx;
        cache()->put($cacheKey, [
            'type' => 'package',
            'user_id' => $user->id,
            'package_id' => $packageId,
            'amount' => (float) $remainingAmount,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $gw_param = 'watchpay_trx=';
        if ($gateway === 'simplypay') $gw_param = 'simplypay_trx=';
        if ($gateway === 'rupeerush') $gw_param = 'rupeerush_trx=';
        if ($gateway === 'custom_qr') $gw_param = 'custom_qr_trx=';
        
        $base = request()->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/user/package-payment?' . $gw_param . urlencode($trx) . '&amount=' . $remainingAmount . '&package_id=' . $packageId . '&package_name=' . urlencode($package['name']);
        $notifyUrl = $base . '/ipn/' . $gateway;
        try {
            if ($gateway === 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => $remainingAmount,
                    'notifyUrl' => $notifyUrl,
                    'returnUrl' => $pageUrl,
                    'name' => $user->fullname ?: $user->username,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'attach' => 'Package: ' . $package['name'],
                ]);
                $paymentUrl = $sp['pay_link'];
            } elseif ($gateway === 'rupeerush') {
                $ap = \App\Lib\RupeeRushGateway::createPayment([
                    'outTradeNo' => $trx,
                    'totalAmount' => $remainingAmount,
                    'notifyUrl' => $notifyUrl,
                    'payViewUrl' => $pageUrl,
                    'payName' => $user->fullname ?: $user->username,
                    'payEmail' => $user->email,
                    'payPhone' => $user->mobile,
                ]);
                $paymentUrl = $ap['pay_link'];
            } elseif ($gateway === 'custom_qr') {
                $gw = \App\Models\Gateway::where('alias', 'custom_qr')->first();
                $qrImages = $gw->extra ?? [];
                $fullQrImages = array_map(function($img) {
                    return asset(getFilePath('gateway') . '/' . $img);
                }, (is_string($qrImages) ? json_decode($qrImages, true) : (array)$qrImages));
                
                return responseSuccess('initiated', ['Manual QR tracking initiated'], [
                    'payment_url' => $pageUrl . '&method=custom_qr',
                    'is_manual' => true,
                    'qr_images' => $fullQrImages,
                    'trx' => $trx,
                    'amount' => (float) $remainingAmount,
                ]);
            } else {
                $wp = \App\Lib\WatchPayGateway::createWebPayment(
                    $trx,
                    (float) $remainingAmount,
                    'Package: ' . $package['name'],
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            return responseError('payment_gateway_error', [$e->getMessage() ?: 'Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $paymentUrl,
            'trx' => $trx,
            'amount' => $remainingAmount,
            'package_id' => $packageId,
            'package_name' => $package['name'],
            'gateway_name' => ($gateway === 'simplypay' ? 'SimplyPay' : ($gateway === 'rupeerush' ? 'RupeeRush' : 'WatchPay')),
        ]);
    }

    /**
     * Dummy Gateway Payment Handler
     * This simulates payment processing
     */
    public function dummyGatewayPayment(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
            'package_id' => 'required|integer|in:1,2,3,4,5',
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush',
        ]);

        $user = auth()->user();
        $packageId = $request->package_id;
        $gateway = $request->input('gateway', 'watchpay');

        $packagesData = [
            1 => ['name' => 'AdsLite', 'price' => 1499],
            2 => ['name' => 'AdsPro', 'price' => 2999],
            3 => ['name' => 'AdsSupreme', 'price' => 5999],
            4 => ['name' => 'AdsPremium', 'price' => 9999],
            5 => ['name' => 'AdsPremium+', 'price' => 15999],
        ];

        $package = $packagesData[$packageId] ?? null;

        if (!$package) {
            return responseError('package_not_found', ['Package not found']);
        }

        // Get current package for remaining amount calculation
        $currentOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->latest()
            ->first();

        $currentPackageId = $currentOrder ? $currentOrder->package_id : 0;
        $remainingAmount = 0;
        
        if ($currentOrder) {
            $currentPackagePrice = $packagesData[$currentPackageId]['price'] ?? 0;
            if ($package['price'] > $currentPackagePrice) {
                $remainingAmount = $package['price'] - $currentPackagePrice;
            }
        } else {
            $remainingAmount = $package['price'];
        }

        $gateway = $request->input('gateway', 'watchpay');
        if (!in_array($gateway, ['simplypay', 'watchpay', 'rupeerush', 'custom_qr'])) {
            $gateway = 'watchpay';
        }

        // Gateway verification
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        if ($gateway === 'custom_qr') $cachePrefix = 'custom_qr_payment_';
        
        $cacheKey = $cachePrefix . $request->trx;
        $session = cache()->get($cacheKey);
        if (!is_array($session) || ($session['type'] ?? '') !== 'package' || (int)($session['user_id'] ?? 0) !== (int)$user->id) {
            return responseError('payment_not_found', ['Payment session not found. Please initiate payment again.']);
        }
        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        // Gateway verified: activate package without using wallet
        $gatewayName = ($gateway === 'simplypay' ? 'SimplyPay' : ($gateway === 'rupeerush' ? 'RupeeRush' : 'WatchPay'));
        return $this->processPackagePurchase($user, $packageId, $package, $remainingAmount, $currentOrder, (string) $request->trx, $gatewayName);
    }
}
