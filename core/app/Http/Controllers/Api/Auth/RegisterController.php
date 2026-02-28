<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Lib\AgentCommission;
use App\Lib\DirectAffiliateCommission;
use App\Lib\PassiveCommission;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\AdPackageOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Central package pricing used in registration/payment.
     * Keep in-sync with PackageController.
     */
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

    private static function packageMeta(int $packageId): array
    {
        $data = static::packagesData();
        return $data[$packageId] ?? ['name' => 'Package', 'price' => 0];
    }

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
     * Backward compatible:
     * - no discount: ref|pkg
     * - with discount: ref|pkg|discount
     */
    private static function packageSig(string $refCode, int $pkgId, ?int $discount = null): string
    {
        $payload = strtolower(trim($refCode)) . '|' . (int) $pkgId;
        if ($discount !== null) {
            $payload .= '|' . (int) $discount;
        }
        return hash_hmac('sha256', $payload, static::hmacKey());
    }

    /**
     * Resolve referrer from ref param: ADS + user_id (e.g. ADS1221) or legacy username
     */
    private static function findReferrerByRef(string $ref): ?User
    {
        $ref = strtoupper(trim($ref));
        if (preg_match('/^ADS(\d+)$/i', $ref, $m)) {
            return User::where('id', (int) $m[1])->where('status', Status::USER_ACTIVE)->first();
        }
        // Fallback to username (case-insensitive)
        return User::where('username', $ref)->where('status', Status::USER_ACTIVE)->first();
    }

    /**
     * Get referrer/affiliate info by ref code (ADS + user id) or username
     */
    public function getReferrerInfo(Request $request)
    {
        $request->validate([
            'ref' => 'required|string',
        ]);

        $referrer = static::findReferrerByRef($request->ref);

        if (!$referrer) {
            return responseError('referrer_not_found', ['Referrer not found or inactive']);
        }

        return responseSuccess('referrer_info', ['Referrer information retrieved'], [
            'id' => $referrer->id,
            'username' => $referrer->username,
            'ref_code' => $referrer->display_id,
            'name' => $referrer->firstname . ' ' . $referrer->lastname,
            'fullname' => $referrer->firstname . ' ' . $referrer->lastname,
            'email' => $referrer->email,
        ]);
    }

    /**
     * Step 1: Validate and store registration data (before payment)
     */
    public function validateRegistration(Request $request)
    {
        if (!gs('registration')) {
            return responseError('registration_disabled', ['Registration is currently disabled']);
        }

        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'fullname' => 'nullable|string|max:255', // Accept fullname, will split if needed
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|min:3|max:255|unique:users|regex:/^[a-z0-9._]+$/', // Optional, can auto-generate (allowed: a-z, 0-9, _, .)
            'password' => ['required', 'confirmed', $passwordValidation],
            'mobile' => 'required|string|max:15|regex:/^[0-9]{10}$/',
            'state' => 'required|string|max:255',
            'country_code' => 'nullable|string|max:10',
            // Product requirement: mandatory acceptance of Terms + Privacy during registration
            'agree' => 'accepted',
            // Sponsor is optional for direct users.
            // If provided, we validate it after basic validation.
            'ref' => 'nullable|string',
            'pkg' => 'required|integer|in:1,2,3,4,5', // Package selection is required
            'pkg_sig' => 'nullable|string|max:128', // Optional signature for package-locked referral links
            'pkg_discount' => 'nullable|integer|min:0', // Optional discount (only valid with signed referral link)
        ]);

        // Handle fullname if provided (split into firstname and lastname)
        if ($request->fullname && !$request->firstname) {
            $nameParts = explode(' ', trim($request->fullname), 2);
            $request->merge([
                'firstname' => $nameParts[0] ?? '',
                'lastname' => $nameParts[1] ?? ''
            ]);
        }

        // Auto-generate username from email if not provided
        if (!$request->username && $request->email) {
            // Strip any illegal characters from the email part
            $username = strtolower(explode('@', $request->email)[0]);
            $username = preg_replace('/[^a-z0-9._]/', '', $username);
            
            // Ensure minimum length for auto-gen
            if (strlen($username) < 3) $username .= 'user';
            
            // Ensure username is unique
            $baseUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = substr($baseUsername, 0, 240) . $counter;
                $counter++;
            }
            $request->merge(['username' => $username]);
        }

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        // If ref is provided, it must match an active user (ADS{user_id} or username)
        $ref = trim((string)($request->ref ?? ''));
        if ($ref !== '') {
            $referrer = static::findReferrerByRef($ref);
            if (!$referrer) {
                return responseError('referrer_not_found', ['Sponsor ID is invalid or inactive']);
            }
        }

        // If a signed package link is used, verify signature to lock package choice (and optional discount)
        $pkgSig = trim((string) ($request->pkg_sig ?? ''));
        $discountRequested = null;
        if ($request->has('pkg_discount') && $request->pkg_discount !== '' && $request->pkg_discount !== null) {
            $discountRequested = (int) $request->pkg_discount;
        }

        // Discount is only honored for signed links.
        // Signed link can be either:
        // - referral link (has ref): sig = HMAC(ref|pkg|disc?)
        // - global link (no ref): sig = HMAC(GLOBAL|pkg|disc?)
        $discount = null;
        if ($pkgSig !== '') {
            $discount = $discountRequested;
            $sigRef = ($ref !== '') ? $ref : 'GLOBAL';
            $expected = static::packageSig($sigRef, (int) $request->pkg, $discount);
            if (!hash_equals($expected, $pkgSig)) {
                return responseError('invalid_package_signature', ['Invalid package referral link. Please use a valid referral link.']);
            }
        }

        // Store registration data temporarily (in session or cache)
        $registrationData = $request->all();
        $pkgMeta = static::packageMeta((int) $request->pkg);
        $pkgPrice = (int) ($pkgMeta['price'] ?? 0);
        $disc = (int) ($discount ?? 0);
        if ($disc > $pkgPrice) {
            $disc = $pkgPrice;
        }

        // Pay selected package price minus discount (discount only possible via signed link)
        $registrationData['discount_amount'] = $disc;
        $registrationData['registration_fee'] = (float) max(0, ($pkgPrice - $disc));
        $registrationData['package_name'] = (string) ($pkgMeta['name'] ?? 'Package');
        
        // Generate temporary registration token
        $regToken = getTrx();
        
        // Store in cache for 30 minutes
        cache()->put('reg_' . $regToken, $registrationData, now()->addMinutes(30));

        return responseSuccess('registration_validated', ['Registration data validated. Please proceed to payment.'], [
            'registration_token' => $regToken,
            'registration_fee' => (float) ($registrationData['registration_fee'] ?? 0),
            'package_name' => (string) ($registrationData['package_name'] ?? 'Package'),
            'currency_symbol' => gs('cur_sym') ?? '₹',
        ]);
    }

    /**
     * Step 2: Initiate registration payment (₹100)
     */
    public function initiateRegistrationPayment(Request $request)
    {
        $request->validate([
            'registration_token' => 'required|string',
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush,custom_qr',
        ]);

        $regToken = $request->registration_token;
        $gateway = $request->input('gateway', 'watchpay');
        
        $gw = \App\Models\Gateway::where('alias', $gateway)->first();
        if (!$gw || $gw->status != 1) {
            return responseError('gateway_unavailable', ['Selected payment gateway is currently unavailable.']);
        }
        
        $registrationData = cache()->get('reg_' . $regToken);

        if (!$registrationData) {
            return responseError('invalid_token', ['Registration token expired or invalid. Please start again.']);
        }

        $pkgMeta = static::packageMeta((int) ($registrationData['pkg'] ?? 0));
        $pkgPrice = (float) ($pkgMeta['price'] ?? 0);
        $disc = (float) ((int) ($registrationData['discount_amount'] ?? 0));
        $registrationFee = (float) max(0, $pkgPrice - $disc);
        
        if ($registrationFee <= 0) {
            return responseError('invalid_package', ['Invalid package amount. Please start again.']);
        }
        $trx = getTrx();

        $paymentData = [
            'registration_token' => $regToken,
            'amount' => $registrationFee,
            'trx' => $trx,
            'type' => 'registration_fee',
            'package_id' => (int) ($registrationData['pkg'] ?? 0),
            'package_name' => (string) ($pkgMeta['name'] ?? 'Package'),
            'discount_amount' => (int) ($registrationData['discount_amount'] ?? 0),
            'gateway' => $gateway,
            'created_at' => now(),
        ];
        cache()->put('reg_payment_' . $trx, $paymentData, now()->addMinutes(30));

        // 🟢 Create a Deposit record so it shows in Admin Panel
        $deposit = new \App\Models\Deposit();
        $deposit->user_id = 0; // 0 means pre-registration
        $deposit->method_code = $gw->code;
        $deposit->amount = $registrationFee;
        $deposit->method_currency = 'INR';
        $deposit->charge = 0;
        $deposit->rate = 1;
        $deposit->final_amount = $registrationFee;
        $deposit->btc_amount = 0;
        $deposit->btc_wallet = '';
        $deposit->trx = $trx;
        $deposit->status = Status::PAYMENT_INITIATE; // 2
        $deposit->from_api = 1;
        $deposit->is_web = 1;
        $deposit->remark = 'registration_fee';
        // Store name and email in detail for admin to see
        $deposit->detail = [
            'name' => ($registrationData['firstname'] ?? '') . ' ' . ($registrationData['lastname'] ?? ''),
            'email' => $registrationData['email'] ?? '',
            'mobile' => $registrationData['mobile'] ?? '',
            'package' => $pkgMeta['name'] ?? 'Package'
        ];
        $deposit->save();

        $gw_param = 'watchpay_trx=';
        if ($gateway === 'simplypay') $gw_param = 'simplypay_trx=';
        if ($gateway === 'rupeerush') $gw_param = 'rupeerush_trx=';
        if ($gateway === 'custom_qr') $gw_param = 'custom_qr_trx=';
        
        // Create Payment via Gateway Selection
        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/register?' . $gw_param . urlencode($trx);
        $notifyUrl = $base . '/ipn/' . $gateway;
        
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        if ($gateway === 'custom_qr') $cachePrefix = 'custom_qr_payment_';
        
        // Store status session for IPN (consistent key for both gateways)
        cache()->put($cachePrefix . $trx, [
            'type' => 'registration',
            'user_id' => 0,
            'amount' => (float) $registrationFee,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        try {
            if ($gateway == 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => (float) $registrationFee,
                    'goodsName' => 'Registration: ' . (string) ($pkgMeta['name'] ?? 'Package'),
                    'returnUrl' => $pageUrl,
                    'notifyUrl' => $notifyUrl,
                    'name' => ($registrationData['firstname'] ?? '') . ' ' . ($registrationData['lastname'] ?? ''),
                    'email' => $registrationData['email'] ?? '',
                    'mobile' => $registrationData['mobile'] ?? '',
                    'attach' => 'registration_temp_id:' . $trx
                ]);
                $paymentUrl = $sp['pay_link'];
            } elseif ($gateway == 'rupeerush') {
                $ap = \App\Lib\RupeeRushGateway::createPayment([
                    'outTradeNo' => $trx,
                    'totalAmount' => (float) $registrationFee,
                    'notifyUrl' => $notifyUrl,
                    'payViewUrl' => $pageUrl,
                    'payName' => ($registrationData['firstname'] ?? '') . ' ' . ($registrationData['lastname'] ?? ''),
                    'payEmail' => $registrationData['email'] ?? '',
                    'payPhone' => $registrationData['mobile'] ?? '',
                ]);
                $paymentUrl = $ap['pay_link'];
            } elseif ($gateway === 'custom_qr') {
                $qrImages = $gw->extra ?? [];
                $fullQrImages = array_map(function($img) {
                    return asset(getFilePath('gateway') . '/' . $img);
                }, (is_string($qrImages) ? json_decode($qrImages, true) : (array)$qrImages));
                
                return responseSuccess('initiated', ['Manual QR tracking initiated'], [
                    'payment_url' => $pageUrl . '&method=custom_qr',
                    'is_manual' => true,
                    'qr_images' => $fullQrImages,
                    'registration_token' => $regToken,
                    'trx' => $trx,
                    'amount' => (float) $registrationFee,
                ]);
            } else {
                $wp = \App\Lib\WatchPayGateway::createWebPayment(
                    $trx,
                    (float) $registrationFee,
                    'Registration: ' . (string) ($pkgMeta['name'] ?? 'Package'),
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            return responseError('payment_gateway_error', ['Payment gateway init failed: ' . $e->getMessage()]);
        }

        return responseSuccess('payment_initiated', ['Payment initialized successfully!'], [
            'payment_url' => $paymentUrl,
            'registration_token' => $regToken,
            'trx' => $trx,
            'amount' => (float) $registrationFee,
            'currency_symbol' => gs('cur_sym') ?? '₹',
            'gateway_name' => $gateway == 'simplypay' ? 'SimplyPay' : ($gateway == 'rupeerush' ? 'RupeeRush' : 'WatchPay'),
            'package_name' => (string) ($pkgMeta['name'] ?? 'Package'),
        ]);
    }

    /**
     * Step 3: Complete registration after payment
     */
    public function register(Request $request)
    {
        if (!gs('registration')) {
            return responseError('registration_disabled', ['Registration is currently disabled']);
        }

        $request->validate([
            'registration_token' => 'required|string',
            'payment_trx' => 'required|string',
        ]);

        $regToken = $request->registration_token;
        $paymentTrx = $request->payment_trx;
        
        // Get registration data
        $registrationData = cache()->get('reg_' . $regToken);
        if (!$registrationData) {
            return responseError('invalid_token', ['Registration token expired. Please start again.']);
        }

        // Verify payment (IPN updates gateway_payment_{trx})
        $paymentData = cache()->get('reg_payment_' . $paymentTrx);
        if (!$paymentData || ($paymentData['registration_token'] ?? null) !== $regToken) {
            return responseError('payment_not_verified', ['Payment not verified. Please complete payment first.']);
        }
        $gateway = $paymentData['gateway'] ?? 'watchpay';
        if (!in_array($gateway, ['simplypay', 'watchpay', 'rupeerush', 'custom_qr'])) {
             $gateway = 'watchpay';
        }
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        if ($gateway === 'custom_qr') $cachePrefix = 'custom_qr_payment_';
        
        $gwSession = cache()->get($cachePrefix . $paymentTrx);
        $gwOk = is_array($gwSession) && (($gwSession['status'] ?? '') === 'success') && (($gwSession['registration_token'] ?? null) === $regToken);
        $legacyOk = (($paymentData['status'] ?? '') === 'success');
        
        if (!$gwOk && !$legacyOk) {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        $result = static::finalizeRegistration($regToken, $paymentTrx);
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        $user = $result['user'];
        $token = $user->createToken('auth_token')->plainTextToken;

        return responseSuccess('registration_successful', ['Registration successful'], [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
            'token' => $token,
            'package_info' => $result['package_info'] ?? null,
        ]);
    }

    /**
     * Finalize registration: creates user, links deposit, sends email, processes commissions.
     * Can be called from API or from Admin Approval (PaymentController).
     */
    public static function finalizeRegistration($regToken, $paymentTrx)
    {
        // Get registration data
        $registrationData = cache()->get('reg_' . $regToken);
        if (!$registrationData) {
            return responseError('invalid_token', ['Registration token expired. Please start again.']);
        }

        // Verify payment
        $paymentData = cache()->get('reg_payment_' . $paymentTrx);
        if (!$paymentData) {
            // If called from Admin or IPN, we might not have the cache if it expired, but we have the Deposit record.
            // However, we NEED the registrationData from cache.
            return responseError('payment_not_found', ['Payment data not found in cache.']);
        }

        // Use registration data from cache
        $ref = $registrationData['ref'] ?? null;
        $packageId = $registrationData['pkg'] ?? null;

        // Handle referral: ref = ADS + user_id (e.g. ADS1221) or legacy username
        $referUser = null;
        if ($ref) {
            $referUser = static::findReferrerByRef($ref);
        }

        $pkgMeta = static::packageMeta((int) $packageId);
        $packageName = (string) ($pkgMeta['name'] ?? 'Package');
        $packagePrice = (float) ($pkgMeta['price'] ?? 0);
        $disc = (int) ($registrationData['discount_amount'] ?? 0);
        if ($disc < 0) $disc = 0;
        if ($disc > (int) $packagePrice) $disc = (int) $packagePrice;
        $finalPackagePrice = (float) max(0, ((float) $packagePrice - (float) $disc));

        // Ensure paid amount matches expected
        $expectedAmount = (float) $finalPackagePrice;
        $paidAmount = (float) ($paymentData['amount'] ?? 0);
        if (abs($paidAmount - $expectedAmount) > 0.01) {
            return responseError('payment_amount_mismatch', ['Payment amount mismatch. Please try again.']);
        }

        // Create user
        $user = new User();
        $user->email = strtolower($registrationData['email']);
        $user->firstname = $registrationData['firstname'];
        $user->lastname = $registrationData['lastname'];
        $user->username = strtolower($registrationData['username']);
        $user->password = $registrationData['password']; // Store as plain text as per existing logic
        $user->mobile = $registrationData['mobile'] ?? '';
        $user->country_code = $registrationData['country_code'] ?? '';
        $user->ref_by = $referUser ? $referUser->id : 0;
        
        // --- Branch Tagging Logic ---
        $branchId = null;
        if ($referUser) {
            // Inheritance: if referrer has a branch, child gets it.
            if ($referUser->branch_id) {
                $branchId = $referUser->branch_id;
            } else {
                // Top-Level Check: is this referrer a partner top-ID?
                $partnerAdmin = \App\Models\Admin::where('user_id', $referUser->id)->first();
                if ($partnerAdmin) {
                    $branchId = $referUser->id; // Using user_id as branch identifier
                }
            }
        }
        $user->branch_id = $branchId;
        if ($branchId) {
            $user->branch_serial = \App\Models\User::where('branch_id', $branchId)->max('branch_serial') + 1;
        }
        // ----------------------------
        $user->kv = gs('kv') ? Status::KYC_UNVERIFIED : Status::KYC_VERIFIED;
        $user->ev = gs('ev') ? Status::UNVERIFIED : Status::VERIFIED;
        $user->sv = gs('sv') ? Status::UNVERIFIED : Status::VERIFIED;
        $user->ts = Status::DISABLE;
        $user->tv = Status::ENABLE;
        $user->status = Status::USER_ACTIVE;
        $user->save();

        // 🟢 Link the temporary deposit to the now-confirmed user
        \App\Models\Deposit::where('trx', $paymentTrx)->update(['user_id' => $user->id]);

        // Send welcome email notification (non-blocking)
        try {
            $siteName = gs('site_name') ?? 'Our Platform';
            $loginUrl = url('/login');
            $refCode  = 'ADS' . $user->id;

            $message = '<p>Hi <strong>' . e($user->fullname) . '</strong>,</p>'
                . '<p>Your account has been created successfully on <strong>' . e($siteName) . '</strong>.</p>'
                . '<p><strong>Username:</strong> ' . e($user->username) . '<br>'
                . '<strong>Referral Code:</strong> ' . e($refCode) . '</p>'
                . '<p>You can login here: <a href="' . e($loginUrl) . '">' . e($loginUrl) . '</a></p>'
                . '<p>Thank you.</p>';

            notify($user, 'DEFAULT', [
                'subject' => 'Welcome to ' . $siteName,
                'message' => $message,
            ], ['email']);
        } catch (\Throwable $e) {
            // Never break registration because of email failure
        }

        // Create transaction for package payment (paid during registration)
        $regFeeTrx = getTrx();
        $regFeeTransaction = new Transaction();
        $regFeeTransaction->user_id = $user->id;
        $regFeeTransaction->amount = (float) ($paymentData['amount'] ?? $finalPackagePrice);
        $regFeeTransaction->post_balance = $user->balance;
        $regFeeTransaction->charge = 0;
        $regFeeTransaction->trx_type = '-';
        $regFeeTransaction->details = 'Package Payment - ' . $packageName;
        $regFeeTransaction->trx = $regFeeTrx;
        $regFeeTransaction->remark = 'registration_fee';
        $regFeeTransaction->save();

        // Create admin notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();

        // Login log
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();
        
        if ($exist) {
            $userLogin->longitude = $exist->longitude;
            $userLogin->latitude = $exist->latitude;
            $userLogin->city = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country = $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude = isset($info['long']) ? implode(',', $info['long']) : '';
            $userLogin->latitude = isset($info['lat']) ? implode(',', $info['lat']) : '';
            $userLogin->city = isset($info['city']) ? implode(',', $info['city']) : '';
            $userLogin->country_code = isset($info['code']) ? implode(',', $info['code']) : '';
            $userLogin->country = isset($info['country']) ? implode(',', $info['country']) : '';
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;
        $userLogin->browser = isset($userAgent['browser']) ? $userAgent['browser'] : '';
        $userLogin->os = isset($userAgent['os_platform']) ? $userAgent['os_platform'] : '';
        $userLogin->save();

        // Activate selected package immediately (payment already completed)
        if ($packageId && $finalPackagePrice > 0) {
            AdPackageOrder::create([
                'user_id' => $user->id,
                'package_id' => $packageId,
                'amount' => $finalPackagePrice,
                'status' => 1,
                'expires_at' => now()->addDays(30),
            ]);
        }

        // Direct affiliate commission (ALL users) – package-wise fixed amount (Master Admin controlled)
        try {
            if ($packageId) {
                DirectAffiliateCommission::creditForPackage(
                    $user,
                    (int) $packageId,
                    (float) $finalPackagePrice,
                    (string) $regFeeTrx,
                    (string) $packageName
                );

                PassiveCommission::creditForPackage(
                    $user,
                    (int) $packageId,
                    (float) $finalPackagePrice,
                    (string) $regFeeTrx,
                    (string) $packageName
                );
            }
        } catch (\Throwable $e) {
            // non-blocking
        }

        // Agent commission (only if sponsor is Agent)
        try {
            if ($user->ref_by > 0) {
                // Determine if special discount link was used (signed link)
                $isSpecialLink = !empty($registrationData['pkg_sig']);
                $commissionType = $isSpecialLink ? 'special_discount' : 'registration';
                
                AgentCommission::process(
                    (int) $user->ref_by,
                    $commissionType,
                    (float) $finalPackagePrice,
                    (string) $regFeeTrx,
                    'Agent commission from User#' . (int) $user->id . ' – Package: ' . $packageName . ($isSpecialLink ? ' (Special Link)' : '') . ' | Base: ₹' . (float) $finalPackagePrice,
                    ['plan_type' => 'package', 'plan_id' => (int) $packageId]
                );
            }
        } catch (\Throwable $e) {
            // non-blocking
        }

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Clear cache
        cache()->forget('reg_' . $regToken);
        cache()->forget('reg_payment_' . $paymentTrx);

        return [
            'user' => $user,
            'package_info' => $packageId ? [
                'package_id' => $packageId,
                'price' => $packagePrice,
                'message' => 'Package activated successfully',
            ] : null,
        ];
    }

    /**
     * Dummy Payment Handler (for testing)
     * In production, this should be replaced with actual gateway callback
     */
    public function dummyPaymentHandler(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
        ]);

        $paymentTrx = $request->trx;
        $session = cache()->get('watchpay_payment_' . $paymentTrx);
        if (!$session) {
            return responseError('invalid_payment', ['Payment transaction not found']);
        }

        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        // Mark legacy reg_payment_ cache as success so existing registration verify passes
        $paymentData = cache()->get('reg_payment_' . $paymentTrx);
        if (is_array($paymentData)) {
            $paymentData['status'] = 'success';
            $paymentData['paid_at'] = now();
            cache()->put('reg_payment_' . $paymentTrx, $paymentData, now()->addMinutes(30));
        }

        return responseSuccess('payment_success', ['Payment verified'], [
            'trx' => $paymentTrx,
            'status' => 'success',
            'message' => 'Payment verified. You can now complete registration.',
        ]);
    }
}
