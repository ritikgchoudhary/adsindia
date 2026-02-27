<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\Conversion;
use App\Models\AdPackageOrder;
use App\Models\Deposit;
use App\Models\CoursePlan;
use App\Models\CoursePlanOrder;
use Illuminate\Support\Facades\Schema;
use App\Lib\AgentCommission;
use App\Lib\CommissionReversal;
use App\Models\Transaction;
use App\Models\AdPackage;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Models\Gateway;
use App\Models\UserCertificate;
use App\Models\NotificationLog;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller {

    /**
     * Get IDs of all users in the referral tree for a given user.
     * Used for agent-restricted views.
     */
    private function getDownlineUserIds($userId): array
    {
        $allIds = [];
        $searchIds = [$userId];

        while (!empty($searchIds)) {
            $nextIds = User::whereIn('ref_by', $searchIds)->pluck('id')->toArray();
            if (empty($nextIds)) break;
            
            // Avoid infinite loops (unlikely but safe)
            $newIds = array_diff($nextIds, $allIds);
            if (empty($newIds)) break;

            $allIds = array_merge($allIds, $newIds);
            $searchIds = $newIds;
        }

        return $allIds;
    }

    /**
     * Common method to get allowed user IDs for the current logged-in admin.
     * If super admin, returns null (meaning ALL).
     */
    private function getAllowedUserIds()
    {
        $admin = auth()->user();
        if ($admin->is_super_admin) return null;
        
        // If it's an agent/manager linked to a user account
        if ($admin->user_id) {
            return $this->getDownlineUserIds($admin->user_id);
        }

        // No link means no users visible by default for security
        return [0];
    }


    /**
     * Normalize kyc_data into a simple object the SPA can render.
     *
     * Stored format (from API KYC submit) is usually:
     * [
     *   { name: "aadhaar_number", type: "text", value: "..." },
     *   { name: "pan_number", type: "text", value: "..." },
     *   { name: "aadhaar_document", type: "file", value: "YYYY/MM/DD/file.jpg|pdf" },
     *   { name: "pan_document", type: "file", value: "YYYY/MM/DD/file.jpg|pdf" },
     * ]
     */
    private function normalizeKycData($raw): array
    {
        if (!$raw) return [];

        // If already an associative object/array with keys, just return as array
        if (is_object($raw)) {
            $asArray = (array) $raw;
            // Heuristic: if it already contains the keys we need, keep it
            if (array_key_exists('aadhaar_number', $asArray) || array_key_exists('pan_number', $asArray) || array_key_exists('aadhaar_image', $asArray) || array_key_exists('pan_image', $asArray)) {
                return $asArray;
            }
        }

        $items = is_array($raw) ? $raw : (array) $raw;
        $out = [
            'aadhaar_number' => null,
            'pan_number' => null,
            // URLs (image-only for *_image; always-available for *_file)
            'aadhaar_image' => null,
            'pan_image' => null,
            'aadhaar_file' => null,
            'pan_file' => null,
        ];

        foreach ($items as $item) {
            $name = null;
            $type = null;
            $value = null;

            if (is_object($item)) {
                $name = $item->name ?? null;
                $type = $item->type ?? null;
                $value = $item->value ?? null;
            } elseif (is_array($item)) {
                $name = $item['name'] ?? null;
                $type = $item['type'] ?? null;
                $value = $item['value'] ?? null;
            }

            if (!$name) continue;

            if ($name === 'aadhaar_number') {
                $out['aadhaar_number'] = $value;
            } elseif ($name === 'pan_number') {
                $out['pan_number'] = $value;
            } elseif ($type === 'file' && ($name === 'aadhaar_document' || $name === 'pan_document') && $value) {
                $relative = getFilePath('verify') . '/' . ltrim((string) $value, '/');
                $url = asset($relative);
                $ext = strtolower(pathinfo((string) $value, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);

                if ($name === 'aadhaar_document') {
                    $out['aadhaar_file'] = $url;
                    if ($isImage) $out['aadhaar_image'] = $url;
                } else {
                    $out['pan_file'] = $url;
                    if ($isImage) $out['pan_image'] = $url;
                }
            }
        }

        // Remove nulls for cleaner payload
        return array_filter($out, fn ($v) => $v !== null);
    }

    /**
     * List all users with pagination (API)
     */
    public function allUsers(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 20);

        $query = User::query()->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('status', 1);
        } elseif ($status === 'banned') {
            $query->where('status', 0);
        } elseif ($status === 'email_unverified') {
            $query->where('ev', 0);
        } elseif ($status === 'kyc_pending') {
            $query->where('kv', Status::KYC_PENDING);
        } elseif ($status === 'kyc_verified' || $status === 'kyc_approved') {
            $query->where('kv', Status::KYC_VERIFIED);
        } elseif ($status === 'partner') {
            $query->whereNotNull('partner_plan_id')->where('partner_plan_valid_until', '>', now());
        } elseif ($status === 'agent') {
            $query->where('is_agent', 1);
        }

        // Apply Downline Restriction
        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $query->whereIn('id', $allowedIds);
        }

        $users = $query->paginate($perPage);

        // Get all user IDs for this page
        $userIds = $users->pluck('id')->toArray();

        // Calculate total deposits (batch)
        $totalDeposits = Deposit::whereIn('user_id', $userIds)
            ->successful()
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(amount) as total_deposit')
            ->pluck('total_deposit', 'user_id')
            ->toArray();

        // Active course plans (batch) – pick the most recent active order per user
        $activeCourseOrders = \App\Models\CoursePlanOrder::whereIn('user_id', $userIds)
            ->active()
            ->with('plan:id,name,price')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        // Active ads plans (batch)
        $activeAdsOrders = \App\Models\AdPackageOrder::whereIn('user_id', $userIds)
            ->active()
            ->with('package:id,name,price')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $users->getCollection()->transform(function ($user) use ($totalDeposits, $activeCourseOrders, $activeAdsOrders) {
            $totalDeposit = $totalDeposits[$user->id] ?? 0;
            $courseOrder = $activeCourseOrders[$user->id] ?? null;
            $adsOrder    = $activeAdsOrders[$user->id] ?? null;

            return [
                'id'         => $user->id,
                'username'   => $user->username,
                'email'      => $user->email,
                'firstname'  => $user->firstname,
                'lastname'   => $user->lastname,
                'mobile'     => $user->mobile ?? '',
                'password'   => $user->password,
                'balance'    => $user->balance,
                'special_agent_balance' => (float)($user->special_agent_balance ?? 0),
                'affiliate_balance' => $user->affiliate_balance,
                'total_deposit' => $totalDeposit,
                'status'     => $user->status == 1 ? 'active' : 'banned',
                'is_agent'   => (bool) ($user->is_agent ?? false),
                'is_special_agent' => (bool) ($user->is_special_agent ?? false),
                'partner_plan_id' => $user->partner_plan_id,
                'partner_plan_valid_until' => $user->partner_plan_valid_until,
                'is_partner' => (bool)($user->partner_plan_id && $user->partner_plan_valid_until && now()->lt($user->partner_plan_valid_until)),
                'ev' => $user->ev,
                'sv' => $user->sv,
                'kv' => $user->kv,
                'has_ad_certificate' => (bool)$user->has_ad_certificate,
                'has_ad_certificate_view' => (bool)$user->has_ad_certificate_view,
                'kyc_data'   => $this->normalizeKycData($user->kyc_data ?? null),
                'kyc_rejection_reason' => $user->kyc_rejection_reason ?? null,
                'referral_code' => $user->referral_code ?? '',
                'referred_by'   => $user->ref_by ? (int) $user->ref_by : 0,
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : null,
                'last_login' => ($user->login_at && $user->login_at instanceof \Carbon\Carbon) ? $user->login_at->format('Y-m-d H:i:s') : $user->login_at,
                'active_course_plan' => $courseOrder ? [
                    'id'    => $courseOrder->plan?->id,
                    'name'  => $courseOrder->plan?->name ?? 'Unknown',
                    'price' => $courseOrder->plan?->price ?? 0,
                ] : null,
                'active_course_plan_id' => $courseOrder?->course_plan_id,
                'active_ads_plan' => $adsOrder ? [
                    'id'    => $adsOrder->package?->id,
                    'name'  => $adsOrder->package?->name ?? 'Unknown',
                    'price' => $adsOrder->package?->price ?? 0,
                ] : null,
                'active_ads_plan_id' => $adsOrder?->package_id,
                'bank_details' => [
                    'account_holder_name' => $user->account_holder_name ?? '',
                    'account_number'      => $user->account_number ?? '',
                    'ifsc_code'           => $user->ifsc_code ?? '',
                    'bank_name'           => $user->bank_name ?? '',
                    'bank_registered_no'  => $user->bank_registered_no ?? '',
                    'branch_name'         => $user->branch_name ?? '',
                    'upi_id'              => $user->upi_id ?? '',
                ],
            ];
        });

        return responseSuccess('users', ['Users retrieved successfully'], [
            'users' => $users->items(),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ]);
    }

    /**
     * Get user segment counts (API)
     */
    public function allUsersCounts()
    {
        $allowedIds = $this->getAllowedUserIds();
        $query = \App\Models\User::query();
        
        if ($allowedIds !== null) {
            $query->whereIn('id', $allowedIds);
        }

        return responseSuccess('user_counts', ['User counts retrieved'], [
            'active' => (clone $query)->where('status', 1)->count(),
            'banned' => (clone $query)->where('status', 0)->count(),
            'kyc_pending' => (clone $query)->where('kv', \App\Constants\Status::KYC_PENDING)->count(),
            'partner' => (clone $query)->whereNotNull('partner_plan_id')->where('partner_plan_valid_until', '>', now())->count(),
            'agent' => (clone $query)->where('is_agent', 1)->count(),
        ]);
    }

    /**
     * Create a new user from Master Admin panel (API).
     * ID is auto-generated by DB (auto-increment; series starts from ADS15000 requirement).
     */
    public function createUser(Request $request)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to create users.']);
        }
        // Phase 2: Simplified Create User form.
        // Accept either (name) OR (firstname+lastname) for backward compatibility.
        $request->validate([
            'name' => 'nullable|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            // Username is auto-generated
            'email' => 'required|email|max:255|unique:users,email',
            'dial_code' => 'nullable|string|max:5',
            // Number (mobile) is required in Phase 2
            'mobile' => 'nullable|string|max:20|unique:users,mobile',
            'state' => 'required|string|max:255',
            'password' => 'required|string|min:6|max:255',
            // Sponsor ID (ADS ID / Username)
            'sponsor_id' => 'nullable|string|max:255',
            // Optional: activate a course package (course plan) for this user
            'course_plan_id' => 'nullable|integer|min:0',
            'ads_plan_id' => 'nullable|integer|min:0',
            // Legacy keys (ignored in Phase 2 UI)
            'ref_mode' => 'nullable|in:normal,referral',
            'ref' => 'nullable|string|max:255',
            'package_id' => 'nullable|integer|in:0,1,2,3,4,5',
        ]);

        // Optional course plan to activate
        $coursePlanId = (int) $request->input('course_plan_id', 0);
        $coursePlan = null;
        if ($coursePlanId > 0) {
            $coursePlan = CoursePlan::where('id', $coursePlanId)->active()->first();
            if (!$coursePlan) {
                return responseError('course_plan_not_found', ['Course package not found or inactive.']);
            }
        }

        // Optional ads plan to activate
        $adsPlanId = (int) $request->input('ads_plan_id', 0);
        $adsPlan = null;
        if ($adsPlanId > 0) {
            $adsPlan = \App\Models\AdPackage::where('id', $adsPlanId)->where('status', 1)->first();
            if (!$adsPlan) {
                return responseError('ads_plan_not_found', ['Ads plan not found or inactive.']);
            }
        }

        // Name -> firstname/lastname
        $name = trim((string) $request->input('name', ''));
        if ($name !== '') {
            $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $firstname = $parts[0] ?? $name;
            $lastname = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
        } else {
            $firstname = trim((string) $request->input('firstname', ''));
            $lastname = trim((string) $request->input('lastname', ''));
            if ($firstname === '' && $lastname === '') {
                return responseError('validation_error', ['Name is required.']);
            }
        }

        // Sponsor -> ref_by
        $sponsorInput = trim((string) $request->input('sponsor_id', ''));
        $refBy = 0;
        if ($sponsorInput !== '') {
            if (preg_match('/^ADS(\d+)$/i', $sponsorInput, $m)) {
                $refBy = (int) $m[1];
            }
            if ($refBy > 0 && !User::where('id', $refBy)->exists()) {
                $refBy = 0;
            }
        }

        $baseUsername = strtolower((string) explode('@', (string) $request->email)[0]);
        $baseUsername = preg_replace('/[^a-z0-9_]/', '_', $baseUsername) ?: 'user';
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $activatedCoursePlan = null;
        DB::beginTransaction();
        try {
            $user = new User();
            $user->firstname = $firstname;
            $user->lastname = $lastname;
            $user->username = $username;
            $user->email = strtolower((string) $request->email);
            $user->dial_code = $request->input('dial_code', '91');
            $user->mobile = $request->mobile;
            $user->state = $request->state;
            $user->ref_by = $refBy;

            // This project uses plain-text password in multiple places (legacy behavior).
            // Keep it consistent so user login works as expected.
            $user->password = $request->password;

            $user->status = Status::USER_ACTIVE; // 1
            $user->ev = Status::VERIFIED; // email verified (skip OTP for admin-created users)
            $user->sv = Status::VERIFIED; // mobile verified (skip OTP for admin-created users)
            $user->kv = Status::KYC_UNVERIFIED;
            $user->balance = 0;
            $user->affiliate_balance = (float) ($user->affiliate_balance ?? 0);
            $user->save();

            // If admin selected a course plan, activate it immediately
            if ($coursePlan) {
                $alreadyActive = CoursePlanOrder::where('user_id', $user->id)
                    ->where('course_plan_id', $coursePlan->id)
                    ->active()
                    ->exists();
                $activatedNow = false;
                if (!$alreadyActive) {
                    // Lifetime (no expiry)
                    $expiresAt = null;
                    CoursePlanOrder::create([
                        'user_id' => $user->id,
                        'course_plan_id' => $coursePlan->id,
                        'amount' => (float) ($coursePlan->price ?? 0),
                        'status' => 1,
                        'expires_at' => $expiresAt,
                    ]);
                    $activatedNow = true;
                }

                $activatedCoursePlan = [
                    'id' => $coursePlan->id,
                    'name' => $coursePlan->name,
                    'validity_days' => (int) ($coursePlan->validity_days ?? 0),
                ];

                // Agent commission on course package activation (admin-created user)
                if ($activatedNow && (int)($user->ref_by ?? 0) > 0) {
                    try {
                        $trx2 = getTrx();
                        AgentCommission::process(
                            (int) $user->ref_by,
                            'registration',
                            (float) ($coursePlan->price ?? 0),
                            $trx2,
                            'Agent commission from User#' . $user->id . ' – Course package (admin activation): ' . ($coursePlan->name ?? '')
                        );
                    } catch (\Throwable $e) {
                        // Never block user creation because of commission failure
                    }
                }
            }

            // If admin selected an ads plan, activate it immediately
            if ($adsPlan) {
                $alreadyAdsActive = \App\Models\AdPackageOrder::where('user_id', $user->id)
                    ->where('package_id', $adsPlan->id)
                    ->active() // Assuming active() scope or status=1
                    ->exists();
                if (!$alreadyAdsActive) {
                    \App\Models\AdPackageOrder::create([
                        'user_id' => $user->id,
                        'package_id' => $adsPlan->id,
                        'amount' => (float) ($adsPlan->price ?? 0),
                        'status' => 1,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return responseError('create_user_failed', ['Failed to create user. Please try again.']);
        }

        return responseSuccess('user_created', ['User created successfully'], [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'mobile' => $user->mobile,
                'dial_code' => $user->dial_code,
                'status' => $user->status == 1 ? 'active' : 'banned',
                'is_agent' => (bool) ($user->is_agent ?? false),
                'is_special_agent' => (bool) ($user->is_special_agent ?? false),
                'special_agent_balance' => (float) ($user->special_agent_balance ?? 0),
                'ev' => $user->ev,
                'sv' => $user->sv,
                'kv' => $user->kv,
                'referral_code' => 'ADS' . $user->id,
                'referred_by' => $user->ref_by ?: null,
                'state' => $user->state ?? null,
            ],
            'activated_course_plan' => $activatedCoursePlan,
        ]);
    }

    /**
     * Ban a user
     */
    public function banUser($id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to ban users.']);
        }
        try {
            $user = User::findOrFail($id);
            $user->status = 0;
            $user->save();
            return responseSuccess('user_banned', ['User banned successfully']);
        } catch (\Exception $e) {
            return responseError('error', ['Failed to ban user']);
        }
    }

    /**
     * Unban a user
     */
    public function unbanUser($id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to unban users.']);
        }
        try {
            $user = User::findOrFail($id);
            $user->status = 1;
            $user->save();
            return responseSuccess('user_unbanned', ['User unbanned successfully']);
        } catch (\Exception $e) {
            return responseError('error', ['Failed to unban user']);
        }
    }

    public function userDetail($id) {
        $user = User::findOrFail($id);
        
        // 1. Downline/Referrals
        $referrals = User::where('ref_by', $user->id)
            ->select('id', 'firstname', 'lastname', 'username', 'email', 'mobile', 'created_at')
            ->orderBy('id', 'desc')
            ->get();

        // 2. Affiliate History (Commissions earned FROM others)
        $commissions = Transaction::where('user_id', $user->id)
            ->where('wallet', 'affiliate')
            ->where('trx_type', '+')
            ->where('remark', 'like', 'agent_%_commission')
            ->orderBy('id', 'desc')
            ->get();

        // 3. Payment History (What this user PAID)
        $payments = Transaction::where('user_id', $user->id)
            ->where('trx_type', '-')
            ->whereIn('remark', [
                'ad_certificate_fee', 
                'ad_certificate_view_fee', 
                'course_plan_purchase_gateway', 
                'ad_plan_purchase', 
                'kyc_fee', 
                'partner_program_gateway',
                'registration_fee'
            ])
            ->orderBy('id', 'desc')
            ->get();

        return responseSuccess('user_detail', ['User details retrieved'], [
            'referrals' => $referrals,
            'commissions' => $commissions,
            'payments' => $payments,
            'has_ad_certificate' => (bool)$user->has_ad_certificate,
            'has_ad_certificate_view' => (bool)$user->has_ad_certificate_view
        ]);
    }

    public function updateAdCertificate(Request $request, $id) {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to update user certificates.']);
        }
        $user = User::findOrFail($id);
        if ($request->has('has_ad_certificate')) {
            $user->has_ad_certificate = $request->boolean('has_ad_certificate');
        }
        if ($request->has('has_ad_certificate_view')) {
            $user->has_ad_certificate_view = $request->boolean('has_ad_certificate_view');
        }
        $user->save();
        
        return responseSuccess('ad_certificate_updated', ["Ad Certificate permissions updated successfully"]);
    }

    /**
     * Update user basic info (Admin API)
     * Fields: Name, Email, Number (mobile), State
     */
    public function updateUserBasic(Request $request, $id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to edit user details.']);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'mobile' => 'nullable|string|max:20|unique:users,mobile,' . $id,
            // Some legacy users may not have state. Allow it to be optional.
            'state' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);

        $name = trim((string) $request->name);
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstname = $parts[0] ?? $name;
        $lastname = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = strtolower((string) $request->email);
        $user->mobile = (string) $request->mobile;
        if ($request->has('state')) {
            $state = trim((string) $request->state);
            $user->state = $state !== '' ? $state : null;
        }
        $user->save();

        return responseSuccess('user_updated', ['User updated successfully'], [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'mobile' => $user->mobile,
                'state' => $user->state ?? null,
                'is_agent' => (bool)($user->is_agent ?? false),
                'is_special_agent' => (bool)($user->is_special_agent ?? false),
                'special_agent_balance' => (float)($user->special_agent_balance ?? 0),
                'referred_by' => $user->ref_by ?: null,
            ],
        ]);
    }

    /**
     * Reset user password (Admin API)
     * Note: project uses plain-text password (legacy behavior).
     */
    public function resetUserPassword(Request $request, $id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to reset user passwords.']);
        }
        $request->validate([
            'password' => 'required|string|min:6|max:255',
        ]);

        $user = User::findOrFail($id);
        $user->password = (string) $request->password;
        $user->save();

        return responseSuccess('password_reset', ['Password reset successfully']);
    }

    /**
     * Mark/unmark a user as Special Agent (Admin API)
     */
    public function setUserSpecialAgent(Request $request, $id)
    {
        $request->validate([
            'is_special_agent' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->is_special_agent = (bool) $request->is_special_agent;
        $user->save();

        return responseSuccess('special_agent_updated', ['Special Agent status updated successfully'], [
            'user' => [
                'id' => $user->id,
                'is_special_agent' => (bool) ($user->is_special_agent ?? false),
            ],
        ]);
    }

    /**
     * Mark/unmark a user as Agent (Admin API)
     */
    public function setUserAgent(Request $request, $id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to manage agent status.']);
        }
        $request->validate([
            'is_agent' => 'nullable|boolean',
            'is_special_agent' => 'nullable|boolean',
        ]);

        $user = User::findOrFail($id);
        if ($request->has('is_agent')) {
            $user->is_agent = (bool) $request->is_agent;
        }
        if ($request->has('is_special_agent')) {
            $user->is_special_agent = (bool) $request->is_special_agent;
        }
        $user->save();

        // When enabling agent mode, ensure settings row exists (admin can edit later).
        // Defaults match current business expectation and remain fully configurable.
        if ((bool) $request->is_agent) {
            try {
                $existing = \DB::table('agent_commission_settings')->where('user_id', $user->id)->first();
                if (!$existing) {
                    $defaults = null;
                    try {
                        $defaults = \DB::table('agent_commission_defaults')->first();
                    } catch (\Throwable $e) {
                        $defaults = null;
                    }
                    \DB::table('agent_commission_settings')->insert([
                        'user_id' => $user->id,
                        'registration_enabled' => (bool) ($defaults->registration_enabled ?? true),
                        'registration_mode' => (string) ($defaults->registration_mode ?? 'percent'),
                        'registration_value' => (float) ($defaults->registration_value ?? 50),
                        'kyc_enabled' => (bool) ($defaults->kyc_enabled ?? true),
                        'kyc_mode' => (string) ($defaults->kyc_mode ?? 'percent'),
                        'kyc_value' => (float) ($defaults->kyc_value ?? 50),
                        'withdraw_fee_enabled' => (bool) ($defaults->withdraw_fee_enabled ?? true),
                        'withdraw_fee_mode' => (string) ($defaults->withdraw_fee_mode ?? 'percent'),
                        'withdraw_fee_value' => (float) ($defaults->withdraw_fee_value ?? 50),
                        'upgrade_enabled' => (bool) ($defaults->upgrade_enabled ?? true),
                        'upgrade_mode' => (string) ($defaults->upgrade_mode ?? 'percent'),
                        'upgrade_value' => (float) ($defaults->upgrade_value ?? 50),
                        'partner_override_enabled' => false,
                        'partner_override_percent' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // non-blocking
            }
        }

        return responseSuccess('agent_updated', ['Agent status updated successfully'], [
            'user' => [
                'id' => $user->id,
                'is_agent' => (bool) ($user->is_agent ?? false),
                'is_special_agent' => (bool) ($user->is_special_agent ?? false),
            ],
        ]);
    }

    /**
     * Get per-agent commission settings (Admin API)
     */
    public function getAgentCommissionSettings($id)
    {
        $user = User::findOrFail($id);
        $settings = \App\Models\AgentCommissionSetting::where('user_id', $user->id)->first();

        return responseSuccess('agent_commissions', ['Agent commission settings retrieved'], [
            'user_id' => $user->id,
            'is_agent' => (bool) ($user->is_agent ?? false),
            'is_special_agent' => (bool) ($user->is_special_agent ?? false),
            'settings' => $settings,
        ]);
    }

    /**
     * Update per-agent commission settings (Admin API)
     */
    public function updateAgentCommissionSettings(Request $request, $id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to edit agent commissions.']);
        }
        $user = User::findOrFail($id);

        $request->validate([
            'registration_enabled' => 'nullable|boolean',
            'registration_mode' => 'nullable|in:percent,fixed',
            'registration_value' => 'nullable|numeric|min:0',

            'kyc_enabled' => 'nullable|boolean',
            'kyc_mode' => 'nullable|in:percent,fixed',
            'kyc_value' => 'nullable|numeric|min:0',

            'withdraw_fee_enabled' => 'nullable|boolean',
            'withdraw_fee_mode' => 'nullable|in:percent,fixed',
            'withdraw_fee_value' => 'nullable|numeric|min:0',

            'upgrade_enabled' => 'nullable|boolean',
            'upgrade_mode' => 'nullable|in:percent,fixed',
            'upgrade_value' => 'nullable|numeric|min:0',

            'partner_override_enabled' => 'nullable|boolean',
            'partner_override_percent' => 'nullable|numeric|min:0|max:100',

            'adplan_enabled' => 'nullable|boolean',
            'adplan_mode' => 'nullable|in:percent,fixed',
            'adplan_value' => 'nullable|numeric|min:0',

            'course_enabled' => 'nullable|boolean',
            'course_mode' => 'nullable|in:percent,fixed',
            'course_value' => 'nullable|numeric|min:0',

            'partner_enabled' => 'nullable|boolean',
            'partner_mode' => 'nullable|in:percent,fixed',
            'partner_value' => 'nullable|numeric|min:0',

            'certificate_enabled' => 'nullable|boolean',
            'certificate_mode' => 'nullable|in:percent,fixed',
            'certificate_value' => 'nullable|numeric|min:0',

            'special_discount_enabled' => 'nullable|boolean',
            'special_discount_mode' => 'nullable|in:percent,fixed',
            'special_discount_value' => 'nullable|numeric|min:0',
            
            'passive_enabled' => 'nullable|boolean',
            'passive_mode' => 'nullable|in:percent,fixed',
            'passive_value' => 'nullable|numeric|min:0',

            'granular_settings' => 'nullable|array',
        ]);

        $settings = \App\Models\AgentCommissionSetting::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'registration_enabled','registration_mode','registration_value',
                'kyc_enabled','kyc_mode','kyc_value',
                'withdraw_fee_enabled','withdraw_fee_mode','withdraw_fee_value',
                'upgrade_enabled','upgrade_mode','upgrade_value',
                'partner_override_enabled','partner_override_percent',
                'adplan_enabled','adplan_mode','adplan_value',
                'course_enabled','course_mode','course_value',
                'partner_enabled','partner_mode','partner_value',
                'certificate_enabled','certificate_mode','certificate_value',
                'special_discount_enabled','special_discount_mode','special_discount_value',
                'passive_enabled', 'passive_mode', 'passive_value',
                'granular_settings'
            ])
        );

        return responseSuccess('agent_commissions_updated', ['Agent commission settings updated'], ['settings' => $settings]);
    }


    /**
     * Direct Affiliate Commission rules (Master Admin API)
     */
    public function directAffiliateCommissions()
    {
        $packages = [
            ['id' => 1, 'name' => 'AdsLite', 'price' => 1499],
            ['id' => 2, 'name' => 'AdsPro', 'price' => 2999],
            ['id' => 3, 'name' => 'AdsSupreme', 'price' => 5999],
            ['id' => 4, 'name' => 'AdsPremium', 'price' => 9999],
            ['id' => 5, 'name' => 'AdsPremium+', 'price' => 15999],
        ];

        $rules = [];
        try {
            $rules = DB::table('direct_affiliate_commissions')->get()->keyBy('package_id')->toArray();
        } catch (\Throwable $e) {
            $rules = [];
        }

        $rows = array_map(function ($p) use ($rules) {
            $r = $rules[$p['id']] ?? null;
            return [
                'package_id' => (int) $p['id'],
                'package_name' => (string) $p['name'],
                'package_price' => (float) $p['price'],
                'enabled' => $r ? (bool) ($r->enabled ?? false) : false,
                'commission_amount' => $r ? (float) ($r->commission_amount ?? 0) : 0,
                'passive_amount' => $r ? (float) ($r->passive_amount ?? 0) : 0,
                'updated_at' => $r ? (string) ($r->updated_at ?? '') : null,
            ];
        }, $packages);

        return responseSuccess('direct_affiliate_commissions', ['Direct affiliate commission rules retrieved'], [
            'rows' => $rows,
        ]);
    }

    public function updateDirectAffiliateCommission(Request $request, $packageId)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'commission_amount' => 'required|numeric|min:0',
            'passive_amount' => 'nullable|numeric|min:0',
        ]);

        $packageId = (int) $packageId;
        $packages = [
            1 => ['name' => 'AdsLite', 'price' => 1499],
            2 => ['name' => 'AdsPro', 'price' => 2999],
            3 => ['name' => 'AdsSupreme', 'price' => 5999],
            4 => ['name' => 'AdsPremium', 'price' => 9999],
            5 => ['name' => 'AdsPremium+', 'price' => 15999],
        ];
        if (!isset($packages[$packageId])) {
            return responseError('invalid_package', ['Invalid package id']);
        }

        $meta = $packages[$packageId];
        $payload = [
            'package_id' => $packageId,
            'package_price' => (float) ($meta['price'] ?? 0),
            'commission_amount' => (float) $request->commission_amount,
            'passive_amount' => (float) ($request->passive_amount ?? 0),
            'enabled' => (bool) $request->enabled,
            'updated_at' => now(),
        ];

        try {
            $existing = DB::table('direct_affiliate_commissions')->where('package_id', $packageId)->first();
            if ($existing) {
                DB::table('direct_affiliate_commissions')->where('package_id', $packageId)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('direct_affiliate_commissions')->insert($payload);
            }
        } catch (\Throwable $e) {
            return responseError('update_failed', ['Failed to update commission rule']);
        }

        $row = DB::table('direct_affiliate_commissions')->where('package_id', $packageId)->first();
        return responseSuccess('direct_affiliate_commission_updated', ['Commission rule updated'], [
            'row' => $row ? (array) $row : null,
        ]);
    }

    /**
     * Special Discount Link Commission rules (Master Admin API)
     * Defines commission earned by referrer when someone buys via a special discount link.
     */
    public function getSpecialLinkCommissions()
    {
        $packages = [
            ['id' => 1, 'name' => 'AdsLite',      'price' => 1499],
            ['id' => 2, 'name' => 'AdsPro',        'price' => 2999],
            ['id' => 3, 'name' => 'AdsSupreme',    'price' => 5999],
            ['id' => 4, 'name' => 'AdsPremium',    'price' => 9999],
            ['id' => 5, 'name' => 'AdsPremium+',   'price' => 15999],
        ];

        $rules = [];
        try {
            $rules = DB::table('special_link_commission_settings')->get()->keyBy('package_id')->toArray();
        } catch (\Throwable $e) {
            $rules = [];
        }

        $rows = array_map(function ($p) use ($rules) {
            $r = $rules[$p['id']] ?? null;
            return [
                'package_id'       => (int) $p['id'],
                'package_name'     => (string) $p['name'],
                'package_price'    => (float) $p['price'],
                'enabled'          => $r ? (bool) ($r->enabled ?? false) : false,
                'mode'             => $r ? (string) ($r->mode ?? 'fixed') : 'fixed',
                'value'            => $r ? (float) ($r->value ?? 0) : 0,
                'updated_at'       => $r ? (string) ($r->updated_at ?? '') : null,
            ];
        }, $packages);

        return responseSuccess('special_link_commissions', ['Special link commission rules retrieved'], [
            'rows' => $rows,
        ]);
    }

    public function updateSpecialLinkCommission(Request $request, $packageId)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'mode'    => 'required|in:percent,fixed',
            'value'   => 'required|numeric|min:0',
        ]);

        $packageId = (int) $packageId;
        $packages = [
            1 => ['name' => 'AdsLite',    'price' => 1499],
            2 => ['name' => 'AdsPro',     'price' => 2999],
            3 => ['name' => 'AdsSupreme', 'price' => 5999],
            4 => ['name' => 'AdsPremium', 'price' => 9999],
            5 => ['name' => 'AdsPremium+','price' => 15999],
        ];
        if (!isset($packages[$packageId])) {
            return responseError('invalid_package', ['Invalid package id']);
        }

        $payload = [
            'package_id' => $packageId,
            'enabled'    => (bool) $request->enabled,
            'mode'       => (string) $request->mode,
            'value'      => (float) $request->value,
            'updated_at' => now(),
        ];

        try {
            $existing = DB::table('special_link_commission_settings')->where('package_id', $packageId)->first();
            if ($existing) {
                DB::table('special_link_commission_settings')->where('package_id', $packageId)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('special_link_commission_settings')->insert($payload);
            }
        } catch (\Throwable $e) {
            return responseError('update_failed', ['Failed to update special link commission rule']);
        }

        $row = DB::table('special_link_commission_settings')->where('package_id', $packageId)->first();
        return responseSuccess('special_link_commission_updated', ['Special link commission rule updated'], [
            'row' => $row ? (array) $row : null,
        ]);
    }

    /**
     * Agent commission defaults (Master Admin API)
     */
    public function getAgentCommissionDefaults()
    {
        $row = null;
        try {
            $row = DB::table('agent_commission_defaults')->first();
            if (!$row) {
                DB::table('agent_commission_defaults')->insert([
                    'registration_enabled' => true,
                    'registration_mode' => 'percent',
                    'registration_value' => 50,
                    'kyc_enabled' => true,
                    'kyc_mode' => 'percent',
                    'kyc_value' => 50,
                    'withdraw_fee_enabled' => true,
                    'withdraw_fee_mode' => 'percent',
                    'withdraw_fee_value' => 50,
                    'upgrade_enabled' => true,
                    'upgrade_mode' => 'percent',
                    'upgrade_value' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $row = DB::table('agent_commission_defaults')->first();
            }
        } catch (\Throwable $e) {
            $row = null;
        }

        return responseSuccess('agent_commission_defaults', ['Agent commission defaults retrieved'], [
            'defaults' => $row ? (array) $row : null,
        ]);
    }

    public function updateAgentCommissionDefaults(Request $request)
    {
        $request->validate([
            'registration_enabled' => 'nullable|boolean',
            'registration_mode' => 'nullable|in:percent,fixed',
            'registration_value' => 'nullable|numeric|min:0',

            'kyc_enabled' => 'nullable|boolean',
            'kyc_mode' => 'nullable|in:percent,fixed',
            'kyc_value' => 'nullable|numeric|min:0',

            'withdraw_fee_enabled' => 'nullable|boolean',
            'withdraw_fee_mode' => 'nullable|in:percent,fixed',
            'withdraw_fee_value' => 'nullable|numeric|min:0',

            'upgrade_enabled' => 'nullable|boolean',
            'upgrade_mode' => 'nullable|in:percent,fixed',
            'upgrade_value' => 'nullable|numeric|min:0',

            'adplan_enabled' => 'nullable|boolean',
            'adplan_mode' => 'nullable|in:percent,fixed',
            'adplan_value' => 'nullable|numeric|min:0',

            'course_enabled' => 'nullable|boolean',
            'course_mode' => 'nullable|in:percent,fixed',
            'course_value' => 'nullable|numeric|min:0',

            'partner_enabled' => 'nullable|boolean',
            'partner_mode' => 'nullable|in:percent,fixed',
            'partner_value' => 'nullable|numeric|min:0',

            'certificate_enabled' => 'nullable|boolean',
            'certificate_mode' => 'nullable|in:percent,fixed',
            'certificate_value' => 'nullable|numeric|min:0',
            
            'passive_enabled' => 'nullable|boolean',
            'passive_mode' => 'nullable|in:percent,fixed',
            'passive_value' => 'nullable|numeric|min:0',
        ]);

        try {
            $existing = DB::table('agent_commission_defaults')->first();
            if (!$existing) {
                DB::table('agent_commission_defaults')->insert([
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $payload = ['updated_at' => now()];
            foreach ([
                'registration_enabled','registration_mode','registration_value',
                'kyc_enabled','kyc_mode','kyc_value',
                'withdraw_fee_enabled','withdraw_fee_mode','withdraw_fee_value',
                'upgrade_enabled','upgrade_mode','upgrade_value',
                'adplan_enabled','adplan_mode','adplan_value',
                'course_enabled','course_mode','course_value',
                'partner_enabled','partner_mode','partner_value',
                'certificate_enabled','certificate_mode','certificate_value',
                'passive_enabled','passive_mode','passive_value',
            ] as $f) {
                if ($request->has($f)) $payload[$f] = $request->input($f);
            }

            DB::table('agent_commission_defaults')->update($payload);
            $row = DB::table('agent_commission_defaults')->first();
            return responseSuccess('agent_commission_defaults_updated', ['Agent commission defaults updated'], [
                'defaults' => $row ? (array) $row : null,
            ]);
        } catch (\Throwable $e) {
            return responseError('update_failed', ['Failed to update defaults']);
        }
    }

    /**
     * Agent upgrade commission rules (Master Admin API)
     */
    public function listAgentUpgradeRules(Request $request)
    {
        $planType = (string) $request->get('plan_type', '');
        if (!in_array($planType, ['ad_plan', 'package', 'course_plan'], true)) {
            return responseError('invalid_plan_type', ['Invalid plan_type']);
        }

        $rules = [];
        try {
            $rules = DB::table('agent_upgrade_commission_rules')
                ->where('plan_type', $planType)
                ->orderBy('plan_id')
                ->get()
                ->map(fn($r) => (array) $r)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            $rules = [];
        }

        $plans = [];
        if ($planType === 'package') {
            $plans = [
                ['id' => 1, 'name' => 'AdsLite', 'price' => 1499],
                ['id' => 2, 'name' => 'AdsPro', 'price' => 2999],
                ['id' => 3, 'name' => 'AdsSupreme', 'price' => 5999],
                ['id' => 4, 'name' => 'AdsPremium', 'price' => 9999],
                ['id' => 5, 'name' => 'AdsPremium+', 'price' => 15999],
            ];
        } elseif ($planType === 'ad_plan') {
            $plans = AdPackage::where('status', 1)->orderBy('id')->get(['id', 'name', 'price'])->map(function ($p) {
                return ['id' => (int) $p->id, 'name' => (string) $p->name, 'price' => (float) $p->price];
            })->values()->all();
        } else { // course_plan
            $plans = CoursePlan::active()->orderBy('id')->get(['id', 'name', 'price'])->map(function ($p) {
                return ['id' => (int) $p->id, 'name' => (string) $p->name, 'price' => (float) $p->price];
            })->values()->all();
        }

        return responseSuccess('agent_upgrade_rules', ['Agent upgrade rules retrieved'], [
            'plan_type' => $planType,
            'plans' => $plans,
            'rules' => $rules,
        ]);
    }

    public function upsertAgentUpgradeRule(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:ad_plan,package,course_plan',
            'plan_id' => 'required|integer|min:1',
            'enabled' => 'required|boolean',
            'mode' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
        ]);

        $planType = (string) $request->plan_type;
        $planId = (int) $request->plan_id;

        $payload = [
            'plan_type' => $planType,
            'plan_id' => $planId,
            'enabled' => (bool) $request->enabled,
            'mode' => (string) $request->mode,
            'value' => (float) $request->value,
            'updated_at' => now(),
        ];

        try {
            $existing = DB::table('agent_upgrade_commission_rules')->where('plan_type', $planType)->where('plan_id', $planId)->first();
            if ($existing) {
                DB::table('agent_upgrade_commission_rules')->where('plan_type', $planType)->where('plan_id', $planId)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('agent_upgrade_commission_rules')->insert($payload);
            }
            $row = DB::table('agent_upgrade_commission_rules')->where('plan_type', $planType)->where('plan_id', $planId)->first();
            return responseSuccess('agent_upgrade_rule_saved', ['Upgrade rule saved'], [
                'rule' => $row ? (array) $row : null,
            ]);
        } catch (\Throwable $e) {
            return responseError('save_failed', ['Failed to save rule']);
        }
    }

    /**
     * Reverse a credited affiliate-wallet commission (for refunds) (Master Admin API)
     */
    public function reverseAffiliateCommission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|min:1',
            'trx' => 'required|string',
            'orig_remark' => 'required|string|max:191',
            'reason' => 'nullable|string|max:255',
        ]);

        $reversed = CommissionReversal::reverseAffiliateCredit(
            (int) $request->user_id,
            (string) $request->trx,
            (string) $request->orig_remark,
            (string) ($request->reason ?: 'Refund / reversal')
        );

        if ($reversed <= 0) {
            return responseError('not_reversed', ['No commission reversed (not found or already reversed).']);
        }

        return responseSuccess('commission_reversed', ['Commission reversed successfully'], [
            'reversed_amount' => (float) $reversed,
        ]);
    }

    /**
     * Change user's sponsor (ref_by) (Admin API)
     * sponsor_id accepts: ADS{ID} or username. Empty clears sponsor.
     */
    public function changeUserSponsor(Request $request, $id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to change user sponsor.']);
        }
        \Log::info("changeUserSponsor called for ID: {$id} with input: " . json_encode($request->all()));
        
        $request->validate([
            'sponsor_id' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $ref = trim((string) $request->input('sponsor_id', ''));

        $refBy = 0;
        if ($ref !== '') {
            if (preg_match('/^ADS(\d+)$/i', $ref, $m)) {
                $refBy = (int) $m[1];
            } elseif (ctype_digit($ref)) {
                $refBy = (int) $ref;
            } else {
                return responseError('invalid_sponsor', ['Sponsor ID must be a number or in ADSxxxx format.']);
            }
        }

        if ($refBy === $user->id) {
            return responseError('invalid_sponsor', ['User cannot be their own sponsor.']);
        }
        if ($refBy > 0 && !User::where('id', $refBy)->exists()) {
            return responseError('invalid_sponsor', ['Sponsor not found.']);
        }

        \DB::table('users')->where('id', $user->id)->update(['ref_by' => $refBy]);
        
        \Log::info("Sponsor update: User {$user->id} ref_by set to {$refBy}");

        return responseSuccess('sponsor_updated', ['Sponsor updated successfully'], [
            'referred_by' => (int) $refBy,
        ]);
    }

    /**
     * Approve user KYC (API)
     */
    public function approveKyc($id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to approve KYC.']);
        }
        try {
            $user = User::findOrFail($id);

            // Gate approval on KYC fee payment (if fee is enabled)
            $general = gs();
            $kycFee = (float) ($general->kyc_fee ?? 0);
            if ($kycFee > 0) {
                $hasPaid = (bool) ($user->has_paid_kyc_fee ?? false);
                if (!$hasPaid) {
                    return responseError('kyc_fee_unpaid', ['KYC fee is not paid yet. Please ask the user to pay the KYC fee before approval.']);
                }
            }

            $user->kv = Status::KYC_VERIFIED;
            $user->kyc_rejection_reason = null;
            $user->save();
            notify($user, 'KYC_APPROVE', []);
            return responseSuccess('kyc_approved', ['KYC approved successfully']);
        } catch (\Exception $e) {
            return responseError('error', ['Failed to approve KYC']);
        }
    }

    /**
     * Reject user KYC (API)
     */
    public function rejectKyc(Request $request, $id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to reject KYC.']);
        }
        $request->validate(['reason' => 'required|string|max:1000']);
        try {
            $user = User::findOrFail($id);
            $user->kv = Status::KYC_UNVERIFIED;
            $user->kyc_rejection_reason = $request->reason;
            // Reset KYC fee flag — user must pay ₹990 again on resubmission
            $user->has_paid_kyc_fee = 0;
            $user->kyc_fee_trx = null;
            $user->kyc_fee_paid_at = null;
            \Log::info("RESET KYC FEE (REJECT) FOR USER $id", ['has_paid' => $user->has_paid_kyc_fee]);
            $user->save();
            notify($user, 'KYC_REJECT', ['reason' => $request->reason]);
            return responseSuccess('kyc_rejected', ['KYC rejected successfully']);
        } catch (\Exception $e) {
            return responseError('error', ['Failed to reject KYC']);
        }
    }

    /**
     * Unapprove / revoke user KYC (API) – set back to unverified
     */
    public function unapproveKyc($id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to unapprove KYC.']);
        }
        try {
            $user = User::findOrFail($id);
            $user->kv = Status::KYC_UNVERIFIED;
            $user->kyc_rejection_reason = null;
            // Reset KYC fee flag — user must pay ₹990 again
            $user->has_paid_kyc_fee = 0;
            $user->kyc_fee_trx = null;
            $user->kyc_fee_paid_at = null;
            \Log::info("RESET KYC FEE (UNAPPROVE) FOR USER $id", ['has_paid' => $user->has_paid_kyc_fee]);
            $user->save();
            return responseSuccess('kyc_unapproved', ['KYC unapproved successfully']);
        } catch (\Exception $e) {
            return responseError('error', ['Failed to unapprove KYC']);
        }
    }

    /**
     * Update user bank account details (Admin)
     */
    public function updateUserBankDetails(Request $request, $id)
    {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to edit bank details.']);
        }
        try {
            $user = User::findOrFail($id);
            
            $validated = $request->validate([
                'account_holder_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:255',
                'ifsc_code' => 'required|string|max:20',
                'bank_name' => 'required|string|max:255',
                'bank_registered_no' => 'nullable|string|max:255',
                'branch_name' => 'nullable|string|max:255',
                'upi_id' => 'nullable|string|max:255',
            ]);

            $user->update($validated);

            return responseSuccess('bank_details_updated', ['Bank details updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return responseError('validation_error', $e->errors());
        } catch (\Exception $e) {
            return responseError('error', ['Failed to update bank details: ' . $e->getMessage()]);
        }
    }

    /**
     * Get all transactions (Admin API)
     */
    public function allTransactions(Request $request)
    {
        $search = $request->get('search');
        $trxType = $request->get('trx_type');
        $remark = $request->get('remark');
        $userId = $request->get('user_id');
        $perPage = $request->get('per_page', 20);

        $query = Transaction::with('user')->orderBy('id', 'desc');

        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $query->whereIn('user_id', $allowedIds);
        }

        $gs = gs();
        if ($gs->admin_transactions_cleared_at) {
            $query->where('created_at', '>', $gs->admin_transactions_cleared_at);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($trxType) {
            $query->where('trx_type', $trxType);
        }

        if ($remark) {
            $query->where('remark', $remark);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $transactions = $query->paginate($perPage);

        $transactions->getCollection()->transform(function ($trx) {
            return [
                'id' => $trx->id,
                'trx' => $trx->trx,
                'trx_type' => $trx->trx_type,
                'amount' => $trx->amount,
                'post_balance' => $trx->post_balance,
                'charge' => $trx->charge ?? 0,
                'details' => $trx->details,
                'remark' => $trx->remark,
                'user_id' => $trx->user_id,
                'user' => $trx->user ? [
                    'id' => $trx->user->id,
                    'username' => $trx->user->username,
                    'email' => $trx->user->email,
                    'firstname' => $trx->user->firstname,
                    'lastname' => $trx->user->lastname,
                ] : null,
                'created_at' => $trx->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $trx->created_at->diffForHumans(),
            ];
        });

        // Get unique remarks for filter
        $remarks = Transaction::distinct()->pluck('remark')->filter()->values();

        return responseSuccess('transactions', ['Transactions retrieved successfully'], [
            'transactions' => $transactions->items(),
            'remarks' => $remarks,
            'total' => $transactions->total(),
            'per_page' => $transactions->perPage(),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
        ]);
    }

    /**
     * Get all deposits (Admin API)
     */
    public function allDeposits(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $perPage = $request->get('per_page', 20);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $gatewayCode = $request->get('method_code');
        $remark = $request->get('remark');

        // Joined with gateways to get method name correctly
        $query = Deposit::with(['user', 'gateway'])->orderBy('id', 'desc');

        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $query->whereIn('user_id', $allowedIds);
        }

        $gs = gs();
        if ($gs->admin_deposits_cleared_at) {
            $query->where('created_at', '>', $gs->admin_deposits_cleared_at);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Always show ONLY successful deposits for History view
        $query->where('status', \App\Constants\Status::PAYMENT_SUCCESS);

        if ($userId) {
            $query->where("user_id", $userId);
        }

        if ($gatewayCode !== null && $gatewayCode !== "") {
            if ($gatewayCode === "manual") {
                $query->where(function($q) {
                    $q->where("approved_by_admin", 1)->orWhere("method_code", 0);
                });
            } else {
                $query->where("method_code", $gatewayCode);
            }
        }

        if ($remark) {
            $query->where("remark", $remark);
        }

        // Date filter (inclusive)
        try {
            if ($startDate) {
                $query->whereDate('created_at', '>=', Carbon::parse($startDate)->toDateString());
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', Carbon::parse($endDate)->toDateString());
            }
        } catch (\Throwable $e) {}

        $deposits = $query->paginate($perPage);

        $deposits->getCollection()->transform(function ($deposit) {
            $statusText = 'Unknown';
            $statusClass = 'secondary';
            
            if ($deposit->status == Status::PAYMENT_PENDING) {
                $statusText = 'Pending';
                $statusClass = 'warning';
            } elseif ($deposit->status == Status::PAYMENT_SUCCESS) {
                if ($deposit->approved_by_admin) {
                    $statusText = 'Admin Approved';
                    $statusClass = 'danger'; // Red for manual
                } else {
                    $statusText = 'Auto Verified';
                    $statusClass = 'success'; // Green for auto
                }
            } elseif ($deposit->status == Status::PAYMENT_REJECT) {
                $statusText = 'Rejected';
                $statusClass = 'danger';
            } elseif ($deposit->status == Status::PAYMENT_INITIATE) {
                $statusText = 'Initiated';
                $statusClass = 'secondary';
            }

            return [
                'id' => $deposit->id,
                'trx' => $deposit->trx,
                'amount' => $deposit->amount,
                'charge' => $deposit->charge ?? 0,
                'after_charge' => $deposit->amount - ($deposit->charge ?? 0),
                'method_code' => $deposit->method_code,
                'method_name' => $deposit->gateway->name ?? $deposit->methodName(),
                'status' => $deposit->status,
                'status_text' => $statusText,
                'status_class' => $statusClass,
                'order_type' => $deposit->remark ? ucwords(str_replace('_', ' ', $deposit->remark)) : 'Deposit',
                'is_manual' => (bool)$deposit->approved_by_admin,
                'user_id' => $deposit->user_id,
                'user' => $deposit->user ? [
                    'id' => $deposit->user->id,
                    'username' => $deposit->user->username,
                    'email' => $deposit->user->email,
                    'firstname' => $deposit->user->firstname,
                    "lastname" => $deposit->user->lastname,
                    "mobile" => (string) $deposit->user->mobile,
                    "ads_id" => "ADS" . $deposit->user->id,
                ] : null,
                'created_at' => $deposit->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $deposit->created_at->diffForHumans(),
                'updated_at' => $deposit->updated_at->format('Y-m-d H:i:s'),
                'approvable' => in_array($deposit->status, [Status::PAYMENT_PENDING, Status::PAYMENT_INITIATE]),
            ];
        });

        // Calculate summary
        $summaryQuery = Deposit::query();
        try {
            if ($startDate) $summaryQuery->whereDate('created_at', '>=', Carbon::parse($startDate)->toDateString());
            if ($endDate) $summaryQuery->whereDate('created_at', '<=', Carbon::parse($endDate)->toDateString());
        } catch (\Throwable $e) {}
        if ($userId) $summaryQuery->where('user_id', $userId);
        if ($gatewayCode !== null && $gatewayCode !== '') {
            if ($gatewayCode === 'manual') {
                $summaryQuery->where(function($q) {
                    $q->where('approved_by_admin', 1)->orWhere('method_code', 0);
                });
            } else {
                $summaryQuery->where('method_code', $gatewayCode);
            }
        }
        if ($remark) $summaryQuery->where('remark', $remark);
        if ($search) {
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $summary = [
            "total" => (clone $summaryQuery)->where("status", Status::PAYMENT_SUCCESS)->count(),
            "successful" => (clone $summaryQuery)->where("status", Status::PAYMENT_SUCCESS)->sum("amount"),
            "pending" => 0,
            "rejected" => 0,
            "initiated" => 0,
        ];
        return responseSuccess('deposits', ['Deposits retrieved successfully'], [
            'deposits' => $deposits->items(),
            'summary' => $summary,
            'total' => $deposits->total(),
            'per_page' => $deposits->perPage(),
            'current_page' => $deposits->currentPage(),
            'last_page' => $deposits->lastPage(),
        ]);
    }


    /**
     * Gateway Deposit Orders (Admin API)
     * Lists deposits that came via payment gateways (method_code 1000-4999).
     * These can be Pending and require admin approval.
     */
    public function gatewayDepositOrders(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $perPage = $request->get('per_page', 20);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Deposit::with('user')
            ->where('method_code', '>=', 1000)
            ->where('method_code', '<', 5000)
            ->orderBy('id', 'desc');

        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $query->whereIn('user_id', $allowedIds);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'pending') {
            $query->where('status', Status::PAYMENT_PENDING);
        } elseif ($status === 'approved' || $status === 'successful') {
            // Gateway deposits are considered "Approved" when status = success
            $query->where('status', Status::PAYMENT_SUCCESS);
        } elseif ($status === 'rejected') {
            $query->where('status', Status::PAYMENT_REJECT);
        } elseif ($status === 'initiated') {
            $query->where('status', Status::PAYMENT_INITIATE);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Date filter (inclusive)
        try {
            if ($startDate) {
                $query->whereDate('created_at', '>=', Carbon::parse($startDate)->toDateString());
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', Carbon::parse($endDate)->toDateString());
            }
        } catch (\Throwable $e) {
            // ignore invalid date formats
        }

        $deposits = $query->paginate($perPage);

        $deposits->getCollection()->transform(function ($deposit) {
            $statusText = 'Unknown';
            $statusClass = 'secondary';

            if ($deposit->status == Status::PAYMENT_PENDING) {
                $statusText = 'Pending';
                $statusClass = 'warning';
            } elseif ($deposit->status == Status::PAYMENT_SUCCESS) {
                $statusText = 'Approved';
                $statusClass = 'success';
            } elseif ($deposit->status == Status::PAYMENT_REJECT) {
                $statusText = 'Rejected';
                $statusClass = 'danger';
            } elseif ($deposit->status == Status::PAYMENT_INITIATE) {
                $statusText = 'Initiated';
                $statusClass = 'secondary';
            }

            return [
                'id' => $deposit->id,
                'trx' => $deposit->trx,
                'amount' => $deposit->amount,
                'charge' => $deposit->charge ?? 0,
                'after_charge' => $deposit->amount - ($deposit->charge ?? 0),
                'method_code' => $deposit->method_code,
                'method_name' => $deposit->methodName(),
                'status' => $deposit->status,
                'status_text' => $statusText,
                'status_class' => $statusClass,
                'user_id' => $deposit->user_id,
                'user' => $deposit->user ? [
                    'id' => $deposit->user->id,
                    'username' => $deposit->user->username,
                    'email' => $deposit->user->email,
                    'firstname' => $deposit->user->firstname,
                    'lastname' => $deposit->user->lastname,
                ] : null,
                'created_at' => $deposit->created_at ? $deposit->created_at->format('Y-m-d H:i:s') : null,
                'created_at_human' => $deposit->created_at ? $deposit->created_at->diffForHumans() : null,
                'updated_at' => $deposit->updated_at ? $deposit->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });

        // Summary (scoped to gateway deposits + filters; not pagination)
        $summaryQuery = Deposit::query()
            ->where('method_code', '>=', 1000)
            ->where('method_code', '<', 5000);

        // apply same filters
        try {
            if ($startDate) {
                $summaryQuery->whereDate('created_at', '>=', Carbon::parse($startDate)->toDateString());
            }
            if ($endDate) {
                $summaryQuery->whereDate('created_at', '<=', Carbon::parse($endDate)->toDateString());
            }
        } catch (\Throwable $e) {}
        if ($userId) {
            $summaryQuery->where('user_id', $userId);
        }
        if ($search) {
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
        if ($status === 'pending') {
            $summaryQuery->where('status', Status::PAYMENT_PENDING);
        } elseif ($status === 'approved' || $status === 'successful') {
            $summaryQuery->where('status', Status::PAYMENT_SUCCESS);
        } elseif ($status === 'rejected') {
            $summaryQuery->where('status', Status::PAYMENT_REJECT);
        } elseif ($status === 'initiated') {
            $summaryQuery->where('status', Status::PAYMENT_INITIATE);
        }

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'successful' => (clone $summaryQuery)->where('status', Status::PAYMENT_SUCCESS)->sum('amount'),
            'pending' => (clone $summaryQuery)->where('status', Status::PAYMENT_PENDING)->sum('amount'),
            'rejected' => (clone $summaryQuery)->where('status', Status::PAYMENT_REJECT)->sum('amount'),
            'initiated' => (clone $summaryQuery)->where('status', Status::PAYMENT_INITIATE)->sum('amount'),
        ];

        return responseSuccess('gateway_deposit_orders', ['Gateway deposit orders retrieved successfully'], [
            'deposits' => $deposits->items(),
            'summary' => $summary,
            'total' => $deposits->total(),
            'per_page' => $deposits->perPage(),
            'current_page' => $deposits->currentPage(),
            'last_page' => $deposits->lastPage(),
        ]);
    }

    /**
     * All Gateway Orders (Admin API)
     * Combines:
     * - Gateway Deposits (deposits table; can be pending/approved/rejected)
     * - Gateway Payments (transactions table; successful, from user panel gateway flows)
     *
     * Filters:
     * - status: pending|approved|rejected|initiated
     * - search: trx, username, email
     * - user_id
     * - start_date, end_date (YYYY-MM-DD)
     */
    public function allGatewayOrders(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $perPage = (int) ($request->get('per_page', 20) ?: 20);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $remarkLabels = [
            'registration_fee'            => 'Registration Fee',
            'kyc_fee'                     => 'KYC Fee',
            'package_upgrade_gateway'     => 'Package Purchase',
            'ad_plan_purchase'            => 'Ad Plan Purchase',
            'partner_program_gateway'     => 'Partner Program',
            'course_plan_purchase_gateway' => 'Course Plan Purchase',
            'ad_certificate_fee'          => 'Ad Certificate (Course)',
            'ad_certificate_view_fee'     => 'Ad Certificate (View)',
            'deposit'                     => 'Deposit',
            'campaign_payment'            => 'Campaign Payment',
        ];

        $type = $request->get('type');

        $pending = (int) Status::PAYMENT_PENDING;
        $success = (int) Status::PAYMENT_SUCCESS;
        $reject = (int) Status::PAYMENT_REJECT;
        $init = (int) Status::PAYMENT_INITIATE;

        $depositQ = DB::table('deposits as d')
            ->leftJoin('users as u', 'u.id', '=', 'd.user_id')
            ->leftJoin('gateways as g', 'g.code', '=', 'd.method_code');

        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $depositQ->whereIn('d.user_id', $allowedIds);
        }

        $depositQ->where('d.method_code', '>=', 500)
            ->where('d.method_code', '<', 10000)
            ->select([
                DB::raw("'deposit' as source"),
                DB::raw('d.id as source_id'),
                DB::raw('d.trx as trx'),
                DB::raw('d.amount as amount'),
                DB::raw('COALESCE(d.charge, 0) as charge'),
                DB::raw('(d.amount - COALESCE(d.charge, 0)) as after_charge'),
                DB::raw('COALESCE(g.name, "Gateway") as method_name'),
                DB::raw('d.status as status'),
                DB::raw("CASE
                    WHEN d.status = {$pending} THEN 'Pending'
                    WHEN d.status = {$success} THEN 'Approved'
                    WHEN d.status = {$reject} THEN 'Rejected'
                    WHEN d.status = {$init} THEN 'Initiated'
                    ELSE 'Unknown' END as status_text"),
                DB::raw("CASE
                    WHEN d.status = {$pending} THEN 'warning'
                    WHEN d.status = {$success} THEN 'success'
                    WHEN d.status = {$reject} THEN 'danger'
                    WHEN d.status = {$init} THEN 'secondary'
                    ELSE 'secondary' END as status_class"),
                DB::raw("COALESCE(d.remark, 'deposit') as remark"),
                DB::raw('d.user_id as user_id'),
                DB::raw('d.detail as detail'),
                DB::raw('u.username as username'),
                DB::raw('u.email as email'),
                DB::raw('u.firstname as firstname'),
                DB::raw('u.lastname as lastname'),
                DB::raw('u.mobile as mobile'),
                DB::raw('d.created_at as created_at'),
                DB::raw("CASE WHEN d.status IN ({$pending}, {$init}) THEN 1 ELSE 0 END as approvable"),
            ]);

        // Withdrawals are managed separately in the Withdrawals section.
        // Only deposit/payment gateway records appear in All Orders.

        $gs = gs();
        if ($gs->admin_orders_cleared_at) {
            $depositQ->where('d.created_at', '>', $gs->admin_orders_cleared_at);
        }

        if ($search) {
            $depositQ->where(function ($q) use ($search) {
                $q->where('d.trx', 'like', "%{$search}%")
                  ->orWhere('u.username', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%")
                  ->orWhere('d.detail', 'like', "%{$search}%");
            });
        }
        if ($userId) {
            $depositQ->where('d.user_id', (int) $userId);
        }
        if ($status === 'pending') {
            $depositQ->where('d.status', $pending);
        } elseif ($status === 'approved' || $status === 'successful') {
            $depositQ->where('d.status', $success);
        } elseif ($status === 'rejected') {
            $depositQ->where('d.status', $reject);
        } elseif ($status === 'initiated') {
            $depositQ->where('d.status', $init);
        }

        if ($type) {
            $depositQ->where('d.remark', $type);
        }

        try {
            if ($startDate) {
                $depositQ->whereDate('d.created_at', '>=', Carbon::parse($startDate)->toDateString());
            }
            if ($endDate) {
                $depositQ->whereDate('d.created_at', '<=', Carbon::parse($endDate)->toDateString());
            }
        } catch (\Throwable $e) {}

        $base = DB::query()->fromSub($depositQ, 'x');

        $page = $base->orderByDesc('x.created_at')->paginate($perPage);

        $items = collect($page->items())->map(function ($r) use ($remarkLabels) {
            $detail = json_decode((string)$r->detail, true);
            $orderType = $remarkLabels[$r->remark] ?? ucwords(str_replace('_', ' ', $r->remark));
            return [
                'id' => (int) $r->source_id,
                'source' => $r->source,
                'source_id' => (int) $r->source_id,
                'trx' => (string) $r->trx,
                'amount' => (float) $r->amount,
                'charge' => (float) $r->charge,
                'after_charge' => (float) $r->after_charge,
                'method_name' => (string) $r->method_name,
                'status' => (int) $r->status,
                'status_text' => (string) $r->status_text,
                'status_class' => (string) $r->status_class,
                'order_type' => $orderType,
                'remark' => $r->remark,
                'approvable' => (int) $r->approvable === 1,
                'user_id' => (int) $r->user_id,
                'user' => [
                    'id' => (int) $r->user_id,
                    'username' => (string) $r->username ?: ($detail['email'] ?? 'Pending Register'),
                    'email' => (string) $r->email ?: ($detail['email'] ?? 'N/A'),
                    'firstname' => (string) $r->firstname ?: ($detail['name'] ?? ''),
                    'lastname' => (string) $r->lastname ?: '',
                    'name' => trim(($r->firstname ?? '') . ' ' . ($r->lastname ?? '')) ?: ($detail['name'] ?? 'N/A'),
                    'mobile' => (string) $r->mobile ?: ($detail['mobile'] ?? ''),
                    'ads_id' => $r->user_id > 0 ? ('ADS' . $r->user_id) : 'Pending',
                ],
                'created_at' => (string) $r->created_at,
            ];
        });
        // Summary
        $summaryQuery = clone $base;
        $summary = [
            'total' => $summaryQuery->count(),
            'successful' => (clone $summaryQuery)->where('status', $success)->sum('amount'),
            'pending' => (clone $summaryQuery)->where('status', $pending)->sum('amount'),
            'rejected' => (clone $summaryQuery)->where('status', $reject)->sum('amount'),
            'initiated' => (clone $summaryQuery)->where('status', $init)->sum('amount'),
        ];

        return responseSuccess('orders', ['Orders retrieved successfully'], [
            'orders' => $items,
            'summary' => $summary,
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }


    /**
     * Gateway Orders (Admin API)
     * Lists all successful gateway payments that are recorded as transactions.
     * Shown in Master Admin -> Deposits as "Gateway Orders".
     */
    public function gatewayOrders(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $allowedRemarks = [
            'registration_fee',
            'kyc_fee',
            'package_upgrade_gateway',
            'ad_plan_purchase',
            'partner_program_gateway',
            'course_plan_purchase_gateway',
            // legacy / other gateway related
            'deposit',
            'campaign_payment',
        ];

        $query = Transaction::with('user')
            ->where('trx_type', '-') // outgoing payment record
            ->whereIn('remark', $allowedRemarks)
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate($perPage);

        $remarkLabels = [
            'registration_fee' => 'Registration Fee',
            'ad_plan_purchase' => 'Ad Plan Purchase',
            'package_upgrade_gateway' => 'Package Upgrade',
            'partner_program_gateway' => 'Partner Program',
            'kyc_fee' => 'KYC Fee',
            'withdraw_gst' => 'Withdrawal GST Fee',
            'course_plan_purchase_gateway' => 'Course Plan Purchase',
            'deposit' => 'Deposit',
            'campaign_payment' => 'Campaign Payment',
        ];

        $orders->getCollection()->transform(function ($t) use ($remarkLabels) {
            return [
                'id' => $t->id,
                'trx' => $t->trx,
                'amount' => $t->amount,
                'charge' => $t->charge ?? 0,
                'after_charge' => $t->amount,
                'method_name' => 'WatchPay',
                'status' => Status::PAYMENT_SUCCESS,
                'status_text' => 'Successful',
                'status_class' => 'success',
                'order_type' => $remarkLabels[$t->remark] ?? (string) $t->remark,
                'remark' => $t->remark,
                'user_id' => $t->user_id,
                'user' => $t->user ? [
                    'id' => $t->user->id,
                    'username' => $t->user->username,
                    'email' => $t->user->email,
                    'firstname' => $t->user->firstname,
                    'lastname' => $t->user->lastname,
                ] : null,
                'created_at' => $t->created_at ? $t->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        // Summary for stats cards
        $totalAmount = (clone $query)->sum('amount');
        $summary = [
            'total' => $orders->total(),
            'successful' => $totalAmount,
            'pending' => 0,
            'rejected' => 0,
            'initiated' => 0,
        ];

        return responseSuccess('gateway_orders', ['Gateway orders retrieved successfully'], [
            'deposits' => $orders->items(), // keep key name compatible with Deposits.vue
            'summary' => $summary,
            'total' => $orders->total(),
            'per_page' => $orders->perPage(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    /**
     * Get all withdrawals (Admin API)
     * Filters:
     * - status: processing|pending|success|approved|rejected
     * - search: trx, username, email
     * - user_id
     * - wallet: main|affiliate
     * - start_date, end_date (YYYY-MM-DD)
     */
    public function allWithdrawals(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $userId = $request->get('user_id');
        $wallet = $request->get('wallet');
        $perPage = $request->get('per_page', 20);

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Withdrawal::with(['user', 'method'])
            ->where('status', '!=', Status::PAYMENT_INITIATE)
            ->orderBy('id', 'desc');

        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $query->whereIn('user_id', $allowedIds);
        }

        $gs = gs();
        if ($gs->admin_withdrawals_cleared_at) {
            $query->where('created_at', '>', $gs->admin_withdrawals_cleared_at);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'processing' || $status === 'pending') {
            $query->where('status', Status::PAYMENT_PENDING);
        } elseif ($status === 'success' || $status === 'approved') {
            $query->where('status', Status::PAYMENT_SUCCESS);
        } elseif ($status === 'rejected') {
            $query->where('status', Status::PAYMENT_REJECT);
        }

        if ($userId) {
            $query->where('user_id', (int) $userId);
        }

        if ($wallet) {
            $query->where('wallet', $wallet);
        }

        // Date filter (inclusive)
        try {
            if ($startDate) {
                $query->whereDate('created_at', '>=', Carbon::parse($startDate)->toDateString());
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', Carbon::parse($endDate)->toDateString());
            }
        } catch (\Throwable $e) {
            // Ignore invalid date formats (frontend should send YYYY-MM-DD)
        }

        $withdrawals = $query->paginate($perPage);

        $withdrawals->getCollection()->transform(function ($w) {
            return [
                'id' => $w->id,
                'trx' => $w->trx,
                'wallet' => $w->wallet ?? 'main',
                'method' => [
                    'id' => $w->method_id,
                    'name' => $w->method->name ?? 'N/A',
                ],
                'amount' => (float) $w->amount,
                'charge' => (float) ($w->charge ?? 0),
                'after_charge' => (float) ($w->after_charge ?? ((float) $w->amount - (float) ($w->charge ?? 0))),
                'final_amount' => (float) ($w->final_amount ?? 0),
                'currency' => $w->currency ?? 'INR',
                'rate' => (float) ($w->rate ?? 1),
                'status' => (int) $w->status,
                'status_badge' => $w->status_badge ?? '',
                'admin_feedback' => $w->admin_feedback ?? '',
                'withdraw_information' => $w->withdraw_information ?? [],
                'user' => $w->user ? [
                    'id' => $w->user->id,
                    'username' => $w->user->username,
                    'email' => $w->user->email,
                    'firstname' => $w->user->firstname,
                    'lastname' => $w->user->lastname,
                    'mobile' => $w->user->mobile,
                    'account_number' => $w->user->account_number,
                    'account_holder_name' => $w->user->account_holder_name,
                    'ifsc_code' => $w->user->ifsc_code,
                    'bank_name' => $w->user->bank_name,
                    'bank_registered_no' => $w->user->bank_registered_no,
                    'upi_id' => $w->user->upi_id,
                    'ads_id' => 'ADS' . (15000 + $w->user->id), // Common format in this project
                ] : null,
                'created_at' => $w->created_at ? $w->created_at->format('Y-m-d H:i:s') : null,
                'created_at_human' => $w->created_at ? $w->created_at->diffForHumans() : null,
                'updated_at' => $w->updated_at ? $w->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });

        // Summary (optionally scoped by wallet/date filters, but not by pagination)
        $summaryQuery = Withdrawal::where('status', '!=', Status::PAYMENT_INITIATE);
        if ($wallet) $summaryQuery->where('wallet', $wallet);
        try {
            if ($startDate) $summaryQuery->whereDate('created_at', '>=', Carbon::parse($startDate)->toDateString());
            if ($endDate) $summaryQuery->whereDate('created_at', '<=', Carbon::parse($endDate)->toDateString());
        } catch (\Throwable $e) {}

        $summary = [
            'total'             => (clone $summaryQuery)->count(),
            'processing'        => (clone $summaryQuery)->where('status', Status::PAYMENT_PENDING)->sum('amount'),
            'processing_count'  => (clone $summaryQuery)->where('status', Status::PAYMENT_PENDING)->count(),
            'success'           => (clone $summaryQuery)->where('status', Status::PAYMENT_SUCCESS)->sum('amount'),
            'success_count'     => (clone $summaryQuery)->where('status', Status::PAYMENT_SUCCESS)->count(),
            'rejected'          => (clone $summaryQuery)->where('status', Status::PAYMENT_REJECT)->sum('amount'),
            'rejected_count'    => (clone $summaryQuery)->where('status', Status::PAYMENT_REJECT)->count(),
        ];

        return responseSuccess('withdrawals', ['Withdrawals retrieved successfully'], [
            'withdrawals'  => $withdrawals->items(),
            'summary'      => $summary,
            'total'        => $withdrawals->total(),
            'per_page'     => $withdrawals->perPage(),
            'current_page' => $withdrawals->currentPage(),
            'last_page'    => $withdrawals->lastPage(),
        ]);
    }

    /**
     * Approve a withdrawal (Admin API)
     */
    public function approveWithdrawal(Request $request)
    {
        if (!$this->checkPermission('edit_withdrawals')) {
            return responseError('permission_denied', ['You do not have permission to approve withdrawals.']);
        }
        $request->validate(['id' => 'required|integer']);

        $withdraw = Withdrawal::where('id', (int) $request->id)
            ->where('status', Status::PAYMENT_PENDING)
            ->with(['user', 'method'])
            ->firstOrFail();

        $withdraw->status = Status::PAYMENT_SUCCESS;
        $withdraw->admin_feedback = $request->input('details');
        $withdraw->save();

        // Agent commission on withdrawal GST fee: credit only on SUCCESS approval (not on payment/init)
        try {
            if ((string) ($withdraw->wallet ?? 'main') === 'main') {
                $info = $withdraw->withdraw_information;
                $arr = json_decode(json_encode($info), true);
                $gstAmount = 0.0;
                $agentId = 0;
                if (is_array($arr)) {
                    foreach ($arr as $row) {
                        $name = (string) ($row['name'] ?? '');
                        $val = $row['value'] ?? null;
                        if ($name === 'gst_amount') $gstAmount = (float) $val;
                        if ($name === 'agent_id') $agentId = (int) $val;
                    }
                }
                if ($agentId <= 0 && $withdraw->user) {
                    $agentId = (int) ($withdraw->user->ref_by ?? 0);
                }
                if ($agentId > 0 && $gstAmount > 0) {
                    AgentCommission::process(
                        $agentId,
                        'withdraw_fee',
                        $gstAmount,
                        (string) $withdraw->trx,
                        'Agent withdrawal-fee commission from User#' . ((int) ($withdraw->user->id ?? 0)) . ' (GST fee) | Base: ₹' . $gstAmount
                    );
                }
            }
        } catch (\Throwable $e) {
            // non-blocking
        }

        if ($withdraw->user) {
            notify($withdraw->user, 'WITHDRAW_APPROVE', [
                'method_name'     => $withdraw->method->name ?? '',
                'method_currency' => $withdraw->currency,
                'method_amount'   => showAmount($withdraw->final_amount, currencyFormat: false),
                'amount'          => showAmount($withdraw->amount, currencyFormat: false),
                'charge'          => showAmount($withdraw->charge, currencyFormat: false),
                'rate'            => showAmount($withdraw->rate, currencyFormat: false),
                'trx'             => $withdraw->trx,
                'admin_details'   => $withdraw->admin_feedback,
            ]);
        }

        return responseSuccess('withdraw_approved', ['Withdrawal approved successfully']);
    }

    /**
     * SimplyPay: Fetch Merchant Balance
     */
    public function simplyPayBalance()
    {
        if (!$this->checkPermission('view_gateways')) {
            return responseError('permission_denied', ['You do not have permission to view balances.']);
        }

        try {
            $res = \App\Lib\SimplyPayGateway::queryBalance();
            if (($res['code'] ?? -1) === 0) {
                return responseSuccess('balance_retrieved', ['SimplyPay balance retrieved'], [
                    'balance' => $res['data']['payoutValue'] ?? 0,
                    'currency' => $res['data']['currency'] ?? 'INR',
                    'raw' => $res['data']
                ]);
            }
            return responseError('balance_error', [$res['msg'] ?? 'SimplyPay returned an error']);
        } catch (\Throwable $e) {
            return responseError('simplypay_connection_error', [$e->getMessage()]);
        }
    }

    /**
     * SimplyPay: Auto Payout (Master Admin Action)
     */
    public function simplyPayAutoPayout(Request $request)
    {
        if (!$this->checkPermission('edit_withdrawals')) {
            return responseError('permission_denied', ['You do not have permission to process auto-payouts.']);
        }
        $request->validate([
            'id' => 'required|integer',
            'password' => 'required|string'
        ]);

        // Security: Check Payout Password from .env
        $payoutPassword = env('PAYOUT_PASSWORD');
        if ($request->password !== $payoutPassword) {
            return responseError('invalid_password', ['Invalid payout authorization password.']);
        }

        $withdraw = Withdrawal::where('id', (int) $request->id)
            ->where('status', Status::PAYMENT_PENDING)
            ->with(['user', 'method'])
            ->firstOrFail();

        // Extract bank/upi details from withdraw_information array
        $infoRows = $withdraw->withdraw_information;
        $payoutType = 'IFSC';
        $name = '';
        $accNo = '';
        $ifsc = '';
        $upiId = '';
        $email = $withdraw->user->email ?? 'user@example.com';
        $mobile = $withdraw->user->mobile ?? '9999999999';

        if (is_array($infoRows)) {
            foreach ($infoRows as $row) {
                $n = (string) ($row['name'] ?? '');
                $v = (string) ($row['value'] ?? '');
                if ($n === 'payout_type') {
                    $payoutType = (strtoupper($v) === 'UPI') ? 'UPI' : 'IFSC';
                }
                if ($n === 'account_holder_name' || $n === 'name') $name = $v;
                if ($n === 'account_number') $accNo = $v;
                if ($n === 'ifsc_code') $ifsc = $v;
                if ($n === 'upi_id') $upiId = $v;
            }
        }

        if (!$name) $name = $withdraw->user->fullname ?: $withdraw->user->username;

        try {
            $res = \App\Lib\SimplyPayGateway::createPayout([
                'merOrderNo' => $withdraw->trx,
                'amount' => $withdraw->final_amount,
                'payoutType' => $payoutType,
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'accountNumber' => $accNo,
                'ifscCode' => $ifsc,
                'upiId' => $upiId,
                'notifyUrl' => url('/ipn/simplypay')
            ]);

            if (($res['code'] ?? -1) === 0) {
                // If API returns SUCCESS immediately (or pending, which is common for payouts)
                $withdraw->admin_feedback = 'Auto Payout initiated via SimplyPay. Status: ' . ($res['msg'] ?? 'Pending');
                $withdraw->save();

                return responseSuccess('payout_initiated', ['SimplyPay Auto-Payout initiated successfully! Please check status in a few minutes.']);
            } else {
                return responseError('payout_failed', [$res['msg'] ?? $res['error'] ?? 'SimplyPay API rejected the request']);
            }

        } catch (\Throwable $e) {
            return responseError('simplypay_error', ['Unable to connect to SimplyPay: ' . $e->getMessage()]);
        }
    }



    /**
     * RupeeRush: Auto Payout (Master Admin Action)
     */
    public function rupeeRushAutoPayout(Request $request)
    {
        if (!$this->checkPermission('edit_withdrawals')) {
            return responseError('permission_denied', ['You do not have permission to process auto-payouts.']);
        }
        $request->validate([
            'id' => 'required|integer',
            'password' => 'required|string'
        ]);

        $payoutPassword = env('PAYOUT_PASSWORD');
        if ($request->password !== $payoutPassword) {
            return responseError('invalid_password', ['Invalid payout authorization password.']);
        }

        $withdraw = Withdrawal::where('id', (int) $request->id)
            ->where('status', Status::PAYMENT_PENDING)
            ->with(['user', 'method'])
            ->firstOrFail();

        $infoRows = $withdraw->withdraw_information;
        $name = '';
        $accNo = '';
        $ifsc = '';
        $upiId = '';
        $email = clone $withdraw->user;
        $email = $email->email ?? 'user@email.com';
        $mobile = $withdraw->user->mobile ?? '+919876543210';
        $isUpi = false;

        if (is_array($infoRows)) {
            foreach ($infoRows as $row) {
                $n = strtolower(string: (string) ($row['name'] ?? ''));
                $v = (string) ($row['value'] ?? '');
                if ($n === 'payout_type' && strtoupper($v) === 'UPI') $isUpi = true;
                if (in_array($n, ['account_holder_name', 'account_name', 'name'])) $name = $v;
                if ($n === 'account_number') $accNo = $v;
                if ($n === 'ifsc_code') $ifsc = $v;
                if ($n === 'upi_id') $upiId = $v;
            }
        }

        if (!$name) $name = $withdraw->user->fullname ?: $withdraw->user->username;
        if (!$name) $name = 'User Name';

        try {
            $payoutData = [
                'outTradeNo' => $withdraw->trx,
                'totalAmount' => $withdraw->final_amount,
                'bankAcctName' => $name,
                'accEmail' => $email,
                'accPhone' => $mobile,
                'notifyUrl' => url('/ipn/rupeerush')
            ];

            if ($isUpi) {
                $payoutData['bankCode'] = 'UPI';
                $payoutData['bankAcctNo'] = $upiId ?: 'missing_upi';
                $payoutData['identityNo'] = $upiId ?: 'missing_upi';
                $payoutData['identityType'] = 'UPI';
            } else {
                $payoutData['bankCode'] = 'IMPS';
                $payoutData['bankAcctNo'] = $accNo ?: 'missing_acct';
                $payoutData['identityNo'] = $ifsc ?: 'IFSCMISSING';
                $payoutData['identityType'] = 'IMPS';
            }

            $res = \App\Lib\RupeeRushGateway::createPayout($payoutData);

            if ($res['success']) {
                $withdraw->admin_feedback = 'Auto Payout initiated via RupeeRush. Status: Pending tracking';
                $withdraw->save();
                return responseSuccess('payout_initiated', ['RupeeRush Auto-Payout initiated successfully! Check status later.']);
            }

        } catch (\Throwable $e) {
            return responseError('rupeerush_error', ['RupeeRush Payout Error: ' . $e->getMessage()]);
        }
    }

    /**
     * RupeeRush: Fetch Merchant Balance
     */
    public function rupeeRushBalance()
    {
        if (!$this->checkPermission('view_gateways')) {
            return responseError('permission_denied', ['You do not have permission to view balances.']);
        }
        try {
            $res = \App\Lib\RupeeRushGateway::getBalance();
            return responseSuccess('balance_retrieved', ['RupeeRush balance retrieved'], [
                'balance' => $res['balance'] ?? 0,
                'currency' => 'INR',
                'raw' => $res['raw'] ?? []
            ]);
        } catch (\Throwable $e) {
            return responseError('rupeerush_connection_error', [$e->getMessage()]);
        }
    }

    /**
     * Reject a withdrawal (Admin API) + refund to correct wallet.
     */
    public function rejectWithdrawal(Request $request)
    {
        if (!$this->checkPermission('edit_withdrawals')) {
            return responseError('permission_denied', ['You do not have permission to reject withdrawals.']);
        }
        $request->validate([
            'id' => 'required|integer',
            'details' => 'required|string|max:1000',
        ]);

        $withdraw = Withdrawal::where('id', (int) $request->id)
            ->where('status', Status::PAYMENT_PENDING)
            ->with(['user', 'method'])
            ->firstOrFail();

        DB::transaction(function () use ($withdraw, $request) {
            $withdraw->status = Status::PAYMENT_REJECT;
            $withdraw->admin_feedback = $request->details;
            $withdraw->save();

            $user = $withdraw->user;
            if (!$user) return;

            $wallet = (string) ($withdraw->wallet ?? 'main');
            if ($wallet === 'affiliate') {
                $user->affiliate_balance = (float) ($user->affiliate_balance ?? 0) + (float) $withdraw->amount;
            } else {
                $user->balance = (float) ($user->balance ?? 0) + (float) $withdraw->amount;
            }
            $user->save();

            $t = new Transaction();
            $t->user_id = $user->id;
            $t->amount = (float) $withdraw->amount;
            $t->post_balance = $wallet === 'affiliate' ? (float) ($user->affiliate_balance ?? 0) : (float) ($user->balance ?? 0);
            $t->charge = 0;
            $t->trx_type = '+';
            $t->remark = 'withdraw_reject';
            $t->details = 'Refunded for withdrawal rejection';
            $t->trx = $withdraw->trx;
            $t->wallet = $wallet;
            $t->save();

            notify($user, 'WITHDRAW_REJECT', [
                'method_name'     => $withdraw->method->name ?? '',
                'method_currency' => $withdraw->currency,
                'method_amount'   => showAmount($withdraw->final_amount, currencyFormat: false),
                'amount'          => showAmount($withdraw->amount, currencyFormat: false),
                'charge'          => showAmount($withdraw->charge, currencyFormat: false),
                'rate'            => showAmount($withdraw->rate, currencyFormat: false),
                'trx'             => $withdraw->trx,
                'post_balance'    => showAmount($t->post_balance ?? 0, currencyFormat: false),
                'admin_details'   => $withdraw->admin_feedback,
            ]);
        });

        return responseSuccess('withdraw_rejected', ['Withdrawal rejected and refunded successfully']);
    }

    public function dashboard() {
        $allowedIds = $this->getAllowedUserIds();

        // Check if API request
        if (request()->expectsJson() || request()->is('api/*')) {
            $userQuery = User::query();
            $campaignQuery = Campaign::query();
            $depositQuery = Deposit::successful();
            $withdrawalQuery = Withdrawal::approved();

            if ($allowedIds !== null) {
                $userQuery->whereIn('id', $allowedIds);
                $campaignQuery->where(function($q) use ($allowedIds) {
                    $q->whereIn('user_id', $allowedIds);
                });
                $depositQuery->whereIn('user_id', $allowedIds);
                $withdrawalQuery->whereIn('user_id', $allowedIds);
            }

            $widget = [
                'total_users'        => (clone $userQuery)->count(),
                'verified_users'     => (clone $userQuery)->where('status', 1)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED)->count(),
                'email_unverified_users' => (clone $userQuery)->where('ev', 0)->count(),
                'mobile_unverified_users' => (clone $userQuery)->where('sv', 0)->count(),
                'total_campaigns'    => $campaignQuery->count(),
                'pending_campaigns'  => (clone $campaignQuery)->where('status', 2)->count(),
                'approved_campaigns' => (clone $campaignQuery)->where('status', 1)->count(),
                'rejected_campaigns' => (clone $campaignQuery)->where('status', 3)->count(),
                'total_courses'      => \App\Models\Course::count(),
                'total_revenue'      => $depositQuery->sum('amount') - $withdrawalQuery->sum('amount'),
                'kyc_pending_users'  => (clone $userQuery)->where('kv', Status::KYC_PENDING)->count(),
            ];

            return responseSuccess('dashboard', ['Dashboard data retrieved successfully'], $widget);
        }

        // Original Blade view response
        $pageTitle = 'Dashboard';
        $widget['total_users']             = User::count();
        $widget['verified_users']          = User::active()->count();
        $widget['email_unverified_users']  = User::emailUnverified()->count();
        $widget['mobile_unverified_users'] = User::mobileUnverified()->count();

        $widget['total_advertisers']             = Advertiser::count();
        $widget['verified_advertisers']          = Advertiser::active()->count();
        $widget['email_unverified_advertisers']  = Advertiser::emailUnverified()->count();
        $widget['mobile_unverified_advertisers'] = Advertiser::mobileUnverified()->count();

        $widget['total_campaigns']    = Campaign::count();
        $widget['pending_campaigns']  = Campaign::pending()->count();
        $widget['approved_campaigns'] = Campaign::approved()->count();
        $widget['rejected_campaigns'] = Campaign::rejected()->count();

        // user Browsing, Country, Operating Log
        $userLoginData = UserLogin::where('created_at', '>=', Carbon::now()->subDays(30))->get(['browser', 'os', 'country']);

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_os_counter'] = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);

        $deposit['total_deposit_amount']   = Deposit::successful()->sum('amount');
        $deposit['total_deposit_pending']  = Deposit::pending()->count();
        $deposit['total_deposit_rejected'] = Deposit::rejected()->count();
        $deposit['total_deposit_charge']   = Deposit::successful()->sum('charge');

        $withdrawals['total_withdraw_amount']   = Withdrawal::approved()->sum('amount');
        $withdrawals['total_withdraw_pending']  = Withdrawal::pending()->count();
        $withdrawals['total_withdraw_rejected'] = Withdrawal::rejected()->count();
        $withdrawals['total_withdraw_charge']   = Withdrawal::approved()->sum('charge');

        return view('admin.dashboard', compact('pageTitle', 'widget', 'chart', 'deposit', 'withdrawals'));
    }

    public function user() {
        $admin = auth()->user();
        if (!$admin) return responseError('unauthorized', ['Unauthorized']);

        return responseSuccess('admin_user', ['Admin user retrieved successfully'], [
            'id' => $admin->id,
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'is_super_admin' => (bool)$admin->is_super_admin,
            'permissions' => $admin->permissions,
        ]);
    }

    public function depositAndWithdrawReport(Request $request) {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }
        $depositsQuery = Deposit::successful()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date);

        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $depositsQuery->whereIn('user_id', $allowedIds);
        }

        $deposits = $depositsQuery
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $withdrawalsQuery = Withdrawal::approved()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date);

        if ($allowedIds !== null) {
            $withdrawalsQuery->whereIn('user_id', $allowedIds);
        }

        $withdrawals = $withdrawalsQuery
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on'  => $date,
                'deposits'    => getAmount($deposits->where('created_on', $date)->first()?->amount ?? 0),
                'withdrawals' => getAmount($withdrawals->where('created_on', $date)->first()?->amount ?? 0),
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Deposited',
                'data' => $data->pluck('deposits'),
            ],
            [
                'name' => 'Withdrawn',
                'data' => $data->pluck('withdrawals'),
            ],
        ];

        return response()->json($report);
    }

    public function transactionReport(Request $request) {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $plusQuery = Transaction::where('trx_type', '+')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date);

        $minusQuery = Transaction::where('trx_type', '-')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date);

        $allowedIds = $this->getAllowedUserIds();
        if ($allowedIds !== null) {
            $plusQuery->whereIn('user_id', $allowedIds);
            $minusQuery->whereIn('user_id', $allowedIds);
        }

        $plusTransactions = $plusQuery
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $minusTransactions = $minusQuery
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'credits'    => getAmount($plusTransactions->where('created_on', $date)->first()?->amount ?? 0),
                'debits'     => getAmount($minusTransactions->where('created_on', $date)->first()?->amount ?? 0),
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Plus Transactions',
                'data' => $data->pluck('credits'),
            ],
            [
                'name' => 'Minus Transactions',
                'data' => $data->pluck('debits'),
            ],
        ];

        return response()->json($report);
    }

    private function getAllDates($startDate, $endDate) {
        $dates       = [];
        $currentDate = new \DateTime($startDate);
        $endDate     = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('d-F-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function getAllMonths($startDate, $endDate) {
        if ($endDate > now()) {
            $endDate = now()->format('Y-m-d');
        }

        $startDate = new \DateTime($startDate);
        $endDate   = new \DateTime($endDate);

        $months = [];

        while ($startDate <= $endDate) {
            $months[] = $startDate->format('F-Y');
            $startDate->modify('+1 month');
        }

        return $months;
    }

    public function profile() {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request) {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password() {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request) {
        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications() {
        $notifications   = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $hasUnread       = AdminNotification::where('is_read', Status::NO)->exists();
        $hasNotification = AdminNotification::exists();
        $pageTitle       = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }

    public function notificationRead($id) {
        $notification          = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport() {
        $pageTitle            = 'Your Listed Report & Request';
        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $url                  = "https://license.viserlab.com/issue/get?" . http_build_query($arr);
        $response             = CurlRequest::curlContent($url);
        $response             = json_decode($response);
        if (!$response || !isset($response->status) || !isset($response->message)) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return to_route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request) {
        $request->validate([
            'type'    => 'required|in:bug,feature',
            'message' => 'required',
        ]);
        $url = 'https://license.viserlab.com/issue/add';

        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $arr['req_type']      = $request->type;
        $arr['message']       = $request->message;
        $response             = CurlRequest::curlPostContent($url, $arr);
        $response             = json_decode($response);
        if (!$response || !isset($response->status) || !isset($response->message)) {
            return to_route('admin.dashboard')->withErrors('Something went wrong');
        }
        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }
        $notify[] = ['success', $response->message];
        return back()->withNotify($notify);
    }

    public function readAllNotification() {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES,
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification() {
        AdminNotification::truncate();
        $notify[] = ['success', 'Notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id) {
        AdminNotification::where('id', $id)->delete();
        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash) {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function commissionLog() {
        $pageTitle      = 'Commission Log';
        $conversionData = Conversion::searchable(['campaign:title', 'user:firstname,lastname,username', 'campaign.advertiser:firstname,lastname,username'])->with(['campaign.advertiser', 'user'])->latest()->paginate(getPaginate());
        return view('admin.commission.log', compact('pageTitle', 'conversionData'));
    }

    public function resetUserData($id) {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to reset user data.']);
        }
        $user = User::findOrFail($id);
        
        DB::transaction(function () use ($user, $id) {
            Transaction::where('user_id', $user->id)->delete();
            Deposit::where('user_id', $user->id)->delete();
            Withdrawal::where('user_id', $user->id)->delete();
            AdPackageOrder::where('user_id', $user->id)->delete();
            CoursePlanOrder::where('user_id', $user->id)->delete();
            UserCertificate::where('user_id', $user->id)->delete();
            NotificationLog::where('user_id', $user->id)->delete();
            \App\Models\Conversion::where('user_id', $user->id)->delete(); // clear conversions/clicks
            
            // Also delete agent settings if any
            DB::table('agent_commission_settings')->where('user_id', $user->id)->delete();
            
            $user->balance = 0;
            $user->affiliate_balance = 0;
            $user->special_agent_balance = 0;
            
            $user->new_user_ads_watched = 0;
            
            $user->kv = Status::KYC_UNVERIFIED;
            $user->kyc_data = null;
            
            // Reset KYC fee flag
            $user->has_paid_kyc_fee = 0;
            $user->kyc_fee_trx = null;
            $user->kyc_fee_paid_at = null;
            \Log::info("RESET KYC FEE (USER DATA RESET) FOR USER $id");
            
            $user->profile_complete = 0;
            
            // Reset network status
            $user->is_agent = 0;
            $user->is_special_agent = 0;
            $user->partner_plan_id = 0;
            $user->partner_plan_valid_until = null;
            
            // Reset certificate
            $user->has_ad_certificate = false;
            $user->has_ad_certificate_view = false;
            
            $user->save();
        });

        return responseSuccess('user_reset', ['User data reset successfully']);
    }

    public function adjustBalance(Request $request, $id) {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to adjust user balance.']);
        }
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'type' => 'required|in:add,subtract',
            'wallet' => 'nullable|in:main,special_agent,affiliate',
            'reason' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $amount = abs($request->amount);
        $reason = $request->reason;
        $wallet = $request->get('wallet', 'main');

        $balanceField = 'balance';
        if ($wallet === 'special_agent') {
            $balanceField = 'special_agent_balance';
        } elseif ($wallet === 'affiliate') {
            $balanceField = 'affiliate_balance';
        }

        if ($request->type == 'add') {
            $user->$balanceField += $amount;
            $trx_type = '+';
        } else {
            if ($user->$balanceField < $amount) {
                return responseError('insufficient_balance', ['User does not have enough ' . str_replace('_', ' ', $wallet) . ' balance to subtract this amount']);
            }
            $user->$balanceField -= $amount;
            $trx_type = '-';
        }

        $user->save();

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $amount;
        $transaction->post_balance = $user->$balanceField;
        $transaction->charge = 0;
        $transaction->trx_type = $trx_type;
        $transaction->details = "Admin adjustment: " . $reason;
        $transaction->trx = getTrx();
        $transaction->remark = 'admin_adjustment';
        $transaction->wallet = $wallet;
        $transaction->save();

        return responseSuccess('balance_adjusted', ['User balance adjusted successfully']);
    }

    public function deleteBankDetails($id) {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to delete bank details.']);
        }
        $user = User::findOrFail($id);
        
        $user->account_holder_name = null;
        $user->account_number = null;
        $user->ifsc_code = null;
        $user->bank_name = null;
        $user->bank_registered_no = null;
        $user->branch_name = null;
        $user->upi_id = null;
        $user->kv = Status::KYC_UNVERIFIED;
        $user->kyc_data = null;
        
        // Reset KYC fee flag — user must pay ₹990 again
        $user->has_paid_kyc_fee = 0;
        $user->kyc_fee_trx = null;
        $user->kyc_fee_paid_at = null;
        
        \Log::info("RESET KYC FEE FOR USER $id", ['has_paid' => $user->has_paid_kyc_fee]);
        $user->save();

        return responseSuccess('bank_deleted', ['Bank details deleted successfully. User must re-KYC.']);
    }

    public function deleteUser($id) {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to delete users.']);
        }
        $user = User::findOrFail($id);
        
        DB::transaction(function () use ($user) {
            Transaction::where('user_id', $user->id)->delete();
            Deposit::where('user_id', $user->id)->delete();
            Withdrawal::where('user_id', $user->id)->delete();
            AdPackageOrder::where('user_id', $user->id)->delete();
            CoursePlanOrder::where('user_id', $user->id)->delete();
            UserCertificate::where('user_id', $user->id)->delete();
            NotificationLog::where('user_id', $user->id)->delete();
            
            $user->delete();
        });

        return responseSuccess('user_deleted', ['User deleted permanently']);
    }

    public function updateCoursePackage(Request $request, $id) {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to update course packages.']);
        }
        $user = User::findOrFail($id);
        $planId = (int)$request->course_plan_id;

        // Deactivate all existing course plans
        \App\Models\CoursePlanOrder::where('user_id', $user->id)->update(['status' => 0]);

        if ($planId > 0) {
            $plan = \App\Models\CoursePlan::findOrFail($planId);
            \App\Models\CoursePlanOrder::create([
                'user_id' => $user->id,
                'course_plan_id' => $planId,
                'amount' => 0,
                'status' => 1,
            ]);
        }

        return responseSuccess('package_updated', ['Course package updated']);
    }

    public function updateAdsPlan(Request $request, $id) {
        if (!$this->checkPermission('edit_users')) {
            return responseError('permission_denied', ['You do not have permission to update ads plans.']);
        }
        $user = User::findOrFail($id);
        $planId = (int)$request->ads_plan_id;

        // Deactivate all existing ad packages
        AdPackageOrder::where('user_id', $user->id)->update(['status' => 0]);

        if ($planId > 0) {
            $plan = AdPackage::findOrFail($planId);
            
            // Calculate validity based on plan price/details
            $validityDays = 30;
            if ($plan->price == 2999) $validityDays = 30;
            elseif ($plan->price == 4999) $validityDays = 60;
            elseif ($plan->price == 7499) $validityDays = 180;
            elseif ($plan->price == 9999) $validityDays = 365;

            AdPackageOrder::create([
                'user_id' => $user->id,
                'package_id' => $planId,
                'amount' => 0,
                'status' => 1,
                'expires_at' => now()->addDays($validityDays),
            ]);
        }

        return responseSuccess('ads_plan_updated', ['Ads plan updated']);
    }

    public function allGateways() {
        $allowedAliases = ['SimplyPay', 'simplypay', 'watchpay', 'WatchPay', 'custom_qr', 'CustomQR', 'Rupeerush', 'rupeerush'];
        $gateways = Gateway::whereIn('alias', $allowedAliases)->orderBy('id', 'desc')->get()->makeVisible('extra');
        foreach ($gateways as $gateway) {
            if ($gateway->alias == 'custom_qr' && $gateway->extra) {
                $images = is_string($gateway->extra) ? json_decode($gateway->extra, true) : (array) $gateway->extra;
                $formattedImages = [];
                foreach ($images as $img) {
                    $formattedImages[] = [
                        'name' => $img,
                        'url' => asset(getFilePath('gateway') . '/' . $img)
                    ];
                }
                $gateway->qr_images = $formattedImages;
            }
        }
        return responseSuccess('gateways', ['Gateways retrieved successfully'], ['gateways' => $gateways]);
    }

    public function toggleGateway($id) {
        if (!$this->checkPermission('manage_settings')) {
            return responseError('permission_denied', ['You do not have permission to manage gateways.']);
        }
        $gateway = Gateway::findOrFail($id);
        $gateway->status = $gateway->status == 1 ? 0 : 1;
        $gateway->save();

        return responseSuccess('gateway_toggled', ['Gateway status updated']);
    }

    public function uploadGatewayQr(Request $request) {
        if (!$this->checkPermission('manage_settings')) {
            return responseError('permission_denied', ['You do not have permission to manage gateways.']);
        }
        $request->validate([
            'qr_images.*' => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png', 'webp'])]
        ]);

        $gateway = Gateway::where('alias', 'custom_qr')->first();
        if (!$gateway) {
            $gateway = new Gateway();
            $gateway->name = 'Custom QR System';
            $gateway->alias = 'custom_qr';
            $gateway->status = 1;
            $gateway->code = 999;
            $gateway->gateway_parameters = json_encode([]);
            $gateway->supported_currencies = [];
            $gateway->save();
        }

        $qrImages = $gateway->extra ?? [];

        if ($request->hasFile('qr_images')) {
            foreach ($request->file('qr_images') as $image) {
                try {
                    $filename = fileUploader($image, getFilePath('gateway'));
                    $qrImages[] = $filename;
                } catch (\Exception $e) {
                    return responseError('upload_failed', ['Image upload failed: ' . $e->getMessage()]);
                }
            }
        }

        $gateway->extra = $qrImages; 
        $gateway->save();

        return responseSuccess('qr_uploaded', ['QR images uploaded']);
    }

    public function removeGatewayQr($index) {
        if (!$this->checkPermission('manage_settings')) {
            return responseError('permission_denied', ['You do not have permission to manage gateways.']);
        }
        $gateway = Gateway::where('alias', 'custom_qr')->firstOrFail();
        $qrImages = $gateway->extra ?? [];

        if (isset($qrImages[$index])) {
            fileManager()->removeFile(getFilePath('gateway') . '/' . $qrImages[$index]);
            unset($qrImages[$index]);
            $qrImages = array_values($qrImages);
            $gateway->extra = $qrImages;
            $gateway->save();
        }

        return responseSuccess('qr_removed', ['QR image removed']);
    }

    public function approveOrder(Request $request, $id) {
        $source = $request->get('source', 'deposit');
        if ($source === 'withdrawal') {
            if (!$this->checkPermission('edit_withdrawals')) {
                return responseError('permission_denied', ['You do not have permission to approve withdrawals.']);
            }
        } else {
            if (!$this->checkPermission('edit_deposits')) {
                return responseError('permission_denied', ['You do not have permission to approve deposits.']);
            }
        }
        if ($source === 'withdrawal') {
            $withdraw = Withdrawal::where('id', $id)->where('status', Status::PAYMENT_PENDING)->with(['user', 'method'])->firstOrFail();
            $withdraw->status = Status::PAYMENT_SUCCESS;
            $withdraw->save();

            notify($withdraw->user, 'WITHDRAW_APPROVE', [
                'method_name' => $withdraw->method->name,
                'method_currency' => $withdraw->currency,
                'method_amount' => showAmount($withdraw->final_amount, currencyFormat: false),
                'amount' => showAmount($withdraw->amount, currencyFormat: false),
                'charge' => showAmount($withdraw->charge, currencyFormat: false),
                'rate' => showAmount($withdraw->rate, currencyFormat: false),
                'trx' => $withdraw->trx,
                'admin_details' => 'Approved via unified orders panel',
            ]);

            return responseSuccess('order_approved', ['Withdrawal approved successfully']);
        } else {
            $deposit = Deposit::where('id', $id)->whereIn('status', [Status::PAYMENT_PENDING, Status::PAYMENT_INITIATE])->firstOrFail();
            \App\Http\Controllers\Gateway\PaymentController::userDataUpdate($deposit, true);
            return responseSuccess('order_approved', ['Order approved successfully']);
        }
    }

    public function rejectOrder(Request $request, $id) {
        $source = $request->get('source', 'deposit');
        if ($source === 'withdrawal') {
            if (!$this->checkPermission('edit_withdrawals')) {
                return responseError('permission_denied', ['You do not have permission to reject withdrawals.']);
            }
        } else {
            if (!$this->checkPermission('edit_deposits')) {
                return responseError('permission_denied', ['You do not have permission to reject deposits.']);
            }
        }
        if ($source === 'withdrawal') {
            $request->validate(['reason' => 'required|string|max:1000']);
            $withdraw = Withdrawal::where('id', $id)->where('status', Status::PAYMENT_PENDING)->with(['user', 'method'])->firstOrFail();

            $withdraw->status = Status::PAYMENT_REJECT;
            $withdraw->admin_feedback = $request->reason;
            $withdraw->save();

            $user = $withdraw->user;
            $user->balance += $withdraw->amount;
            $user->save();

            $transaction = new Transaction();
            $transaction->user_id = $withdraw->user_id;
            $transaction->amount = $withdraw->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '+';
            $transaction->remark = 'withdraw_reject';
            $transaction->details = 'Refunded for withdrawal rejection (Unified Panel)';
            $transaction->trx = $withdraw->trx;
            $transaction->save();

            notify($user, 'WITHDRAW_REJECT', [
                'method_name' => $withdraw->method->name,
                'method_currency' => $withdraw->currency,
                'method_amount' => showAmount($withdraw->final_amount, currencyFormat: false),
                'amount' => showAmount($withdraw->amount, currencyFormat: false),
                'charge' => showAmount($withdraw->charge, currencyFormat: false),
                'rate' => showAmount($withdraw->rate, currencyFormat: false),
                'trx' => $withdraw->trx,
                'post_balance' => showAmount($user->balance, currencyFormat: false),
                'admin_details' => $request->reason,
            ]);

            return responseSuccess('order_rejected', ['Withdrawal rejected successfully']);
        } else {
            $request->validate(['reason' => 'required|string|max:255']);
            $deposit = Deposit::where('id', $id)->whereIn('status', [Status::PAYMENT_PENDING, Status::PAYMENT_INITIATE])->firstOrFail();

            $deposit->admin_feedback = $request->reason;
            $deposit->status = Status::PAYMENT_REJECT;
            $deposit->save();

            notify($deposit->user, 'DEPOSIT_REJECT', [
                'method_name' => $deposit->methodName(),
                'method_currency' => $deposit->method_currency,
                'method_amount' => showAmount($deposit->final_amount, currencyFormat: false),
                'amount' => showAmount($deposit->amount, currencyFormat: false),
                'charge' => showAmount($deposit->charge, currencyFormat: false),
                'rate' => showAmount($deposit->rate, currencyFormat: false),
                'trx' => $deposit->trx,
                'rejection_message' => $request->reason,
            ]);

            return responseSuccess('order_rejected', ['Order rejected successfully']);
        }
    }

    public function deleteOrder(Request $request, $id) {
        $source = $request->get('source', 'deposit');
        if ($source === 'withdrawal') {
            if (!$this->checkPermission('edit_withdrawals')) {
                return responseError('permission_denied', ['You do not have permission to delete withdrawal records.']);
            }
        } else {
            if (!$this->checkPermission('edit_deposits')) {
                return responseError('permission_denied', ['You do not have permission to delete deposit records.']);
            }
        }
        if ($source === 'withdrawal') {
            $withdraw = Withdrawal::findOrFail($id);
            $withdraw->delete();
        } else {
            $deposit = Deposit::findOrFail($id);
            $deposit->delete();
        }
        return responseSuccess('order_deleted', ['Order deleted successfully']);
    }

    // ─── Admin Management (Master Admin) ───────────────────────────────────────

    /** List all admin accounts */
    public function listAdmins()
    {
        if (!auth()->user()->is_super_admin) {
            return responseError('forbidden', ['Only master admins can list other admins.']);
        }
        $admins = \App\Models\Admin::with('user:id,username')->orderBy('id', 'desc')->get()->map(function($a) {
            return [
                'id'         => $a->id,
                'name'       => $a->name,
                'username'   => $a->username,
                'email'      => $a->email,
                'status'     => $a->status ?? 1,
                'is_super_admin' => (bool) $a->is_super_admin,
                'user_id'    => $a->user_id,
                'user_name'  => $a->user?->username,
                'permissions' => $a->permissions,
                'created_at' => $a->created_at ? $a->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return responseSuccess('admins', ['Admins retrieved'], ['admins' => $admins]);
    }

    /** Create a new admin account */
    public function createAdmin(Request $request)
    {
        if (!auth()->user()->is_super_admin) {
            return responseError('forbidden', ['Only master admins can create other admins.']);
        }
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins,username',
            'email'    => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:6|max:255',
        ]);

        $admin = new \App\Models\Admin();
        $admin->name     = trim($request->name);
        $admin->username = trim($request->username);
        $admin->email    = strtolower(trim($request->email));
        $admin->password = \Illuminate\Support\Facades\Hash::make($request->password);
        if (\Illuminate\Support\Facades\Schema::hasColumn('admins', 'status')) {
            $admin->status = 1;
        }
        $admin->save();

        return responseSuccess('admin_created', ['Admin created successfully'], [
            'admin' => [
                'id'       => $admin->id,
                'name'     => $admin->name,
                'username' => $admin->username,
                'email'    => $admin->email,
                'status'   => 1,
                'permissions' => null,
                'user_id'  => null,
                'is_super_admin' => false,
            ]
        ]);
    }

    /** Update admin permissions and linkage */
    public function updateAdmin(Request $request, $id)
    {
        if (!auth()->user()->is_super_admin) {
            return responseError('forbidden', ['Only master admins can update other admins.']);
        }
        $admin = \App\Models\Admin::findOrFail($id);
        
        if ($admin->is_super_admin && auth()->user()->id != $admin->id) {
            return responseError('forbidden', ['You cannot modify another super admin.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,'.$id,
            'user_id' => 'nullable|integer|exists:users,id',
            'permissions' => 'nullable|array',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->user_id = $request->user_id;
        $admin->permissions = $request->permissions;
        $admin->save();

        return responseSuccess('admin_updated', ['Admin settings updated successfully']);
    }

    /** Toggle admin active/inactive status */
    public function toggleAdmin(Request $request, $id)
    {
        if (!auth()->user()->is_super_admin) {
            return responseError('forbidden', ['Only master admins can toggle other admins.']);
        }
        $admin = \App\Models\Admin::findOrFail($id);
        
        // Safety: ID 1 is the Master Admin and cannot be deactivated or deleted
        if ($admin->id == 1) {
            return responseError('cannot_deactivate_super', ['Master Admin account cannot be restricted.']);
        }

        if ($request->user() && $request->user()->id == $id) {
            return responseError('cannot_deactivate_self', ['You cannot deactivate your own access.']);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('admins', 'status')) {
            $admin->status = $admin->status ? 0 : 1;
            $admin->save();
        }
        return responseSuccess('admin_toggled', ['Admin status updated'], ['status' => $admin->status ?? 1]);
    }

    /** Reset admin password */
    public function resetAdminPassword(Request $request, $id)
    {
        if (!auth()->user()->is_super_admin) {
            return responseError('forbidden', ['Only master admins can reset other admin passwords.']);
        }
        $request->validate(['password' => 'required|string|min:6|max:255']);
        $admin = \App\Models\Admin::findOrFail($id);
        $admin->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $admin->save();
        return responseSuccess('admin_password_reset', ['Admin password reset successfully']);
    }

    /** Delete an admin account (cannot delete the currently logged-in admin) */
    public function deleteAdmin(Request $request, $id)
    {
        if (!auth()->user()->is_super_admin) {
            return responseError('forbidden', ['Only master admins can delete other admins.']);
        }
        $currentAdmin = $request->user();
        if ($currentAdmin && $currentAdmin->id == $id) {
            return responseError('cannot_delete_self', ['You cannot delete your own account.']);
        }
        $admin = \App\Models\Admin::findOrFail($id);
        $admin->delete();
        return responseSuccess('admin_deleted', ['Admin deleted successfully']);
    }

    // ─── Reports & Analytics (Master Admin) ────────────────────────────────────

    /** Get comprehensive reports / analytics stats */
    public function reports(Request $request)
    {
        $range = (int) $request->get('range', 30); // days
        $from  = Carbon::now()->subDays($range)->startOfDay();
        $to    = Carbon::now()->endOfDay();

        $allowedIds = $this->getAllowedUserIds();

        // User growth
        $userGrowthQuery = User::whereBetween('created_at', [$from, $to]);
        if ($allowedIds !== null) $userGrowthQuery->whereIn('id', $allowedIds);
        
        $userGrowth = $userGrowthQuery
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'count' => (int)$r->count]);

        // Revenue chart
        $revenueQuery = Deposit::successful()->whereBetween('created_at', [$from, $to]);
        if ($allowedIds !== null) $revenueQuery->whereIn('user_id', $allowedIds);

        $revenueChart = $revenueQuery
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'total' => (float)$r->total]);

        // Withdrawal chart
        $withdrawQuery = Withdrawal::approved()->whereBetween('created_at', [$from, $to]);
        if ($allowedIds !== null) $withdrawQuery->whereIn('user_id', $allowedIds);

        $withdrawChart = $withdrawQuery
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'total' => (float)$r->total]);

        $gs = gs();

        // Summary stats with clearing support
        $totalDepositsQuery = Deposit::successful();
        if ($gs->admin_deposits_cleared_at) $totalDepositsQuery->where('created_at', '>', $gs->admin_deposits_cleared_at);
        if ($allowedIds !== null) $totalDepositsQuery->whereIn('user_id', $allowedIds);
        $totalDeposits = $totalDepositsQuery->sum('amount');

        $totalWithdrawalsQuery = Withdrawal::approved();
        if ($gs->admin_withdrawals_cleared_at) $totalWithdrawalsQuery->where('created_at', '>', $gs->admin_withdrawals_cleared_at);
        if ($allowedIds !== null) $totalWithdrawalsQuery->whereIn('user_id', $allowedIds);
        $totalWithdrawals = $totalWithdrawalsQuery->sum('amount');

        $pendingDepsQuery = Deposit::pending();
        $pendingWithsQuery = Withdrawal::pending();
        $userBaseQuery = User::query();
        if ($allowedIds !== null) {
            $pendingDepsQuery->whereIn('user_id', $allowedIds);
            $pendingWithsQuery->whereIn('user_id', $allowedIds);
            $userBaseQuery->whereIn('id', $allowedIds);
        }

        $pendingDeposits = $pendingDepsQuery->count();
        $pendingWithdrawals = $pendingWithsQuery->count();
        $totalUsers         = (clone $userBaseQuery)->count();
        $activeUsers        = (clone $userBaseQuery)->where('status', 1)->count();
        $bannedUsers        = (clone $userBaseQuery)->where('status', 0)->count();
        $kycPending         = (clone $userBaseQuery)->where('kv', \App\Constants\Status::KYC_PENDING)->count();
        $kycVerified        = (clone $userBaseQuery)->where('kv', \App\Constants\Status::KYC_VERIFIED)->count();
        
        $totalTransactionsQuery = Transaction::query();
        if ($gs->admin_transactions_cleared_at) $totalTransactionsQuery->where('created_at', '>', $gs->admin_transactions_cleared_at);
        if ($allowedIds !== null) $totalTransactionsQuery->whereIn('user_id', $allowedIds);
        $totalTransactions  = $totalTransactionsQuery->count();

        $todayUserQuery = User::whereDate('created_at', Carbon::today());
        $todayDepQuery = Deposit::successful()->whereDate('created_at', Carbon::today());
        if ($allowedIds !== null) {
            $todayUserQuery->whereIn('id', $allowedIds);
            $todayDepQuery->whereIn('user_id', $allowedIds);
        }

        $newUsersToday      = $todayUserQuery->count();
        $depositsToday      = $todayDepQuery->sum('amount');

        return responseSuccess('reports', ['Reports retrieved'], [
            'summary' => [
                'total_deposits'       => (float) $totalDeposits,
                'total_withdrawals'    => (float) $totalWithdrawals,
                'net_revenue'          => (float) ($totalDeposits - $totalWithdrawals),
                'pending_deposits'     => (int) $pendingDeposits,
                'pending_withdrawals'  => (int) $pendingWithdrawals,
                'total_users'          => (int) $totalUsers,
                'active_users'         => (int) $activeUsers,
                'banned_users'         => (int) $bannedUsers,
                'kyc_pending'          => (int) $kycPending,
                'kyc_verified'         => (int) $kycVerified,
                'total_transactions'   => (int) $totalTransactions,
                'new_users_today'      => (int) $newUsersToday,
                'deposits_today'       => (float) $depositsToday,
            ],
            'user_growth'    => $userGrowth,
            'revenue_chart'  => $revenueChart,
            'withdraw_chart' => $withdrawChart,
        ]);
    }

    /**
     * Get all users marked as agents (Admin API)
     */
    public function getAgents()
    {
        $agents = User::where('is_agent', 1)
            ->with('agentCommissionSettings')
            ->orderBy('id', 'desc')
            ->get(['id', 'firstname', 'lastname', 'username', 'email', 'mobile']);
            
        $formatted = $agents->map(function($a) {
            return [
                'id'        => $a->id,
                'firstname' => $a->firstname,
                'lastname'  => $a->lastname,
                'username'  => $a->username,
                'email'     => $a->email,
                'mobile'    => $a->mobile,
                'is_agent'  => true,
                'settings'  => $a->agentCommissionSettings
            ];
        });
            
        return responseSuccess('agents', ['Agents retrieved successfully'], ['agents' => $formatted]);
    }

    /**
     * History Clearing Methods (Master Admin only) - Sets timestamps to hide logs from Admin
     */
    public function clearTransactions()
    {
        $gs = gs();
        $gs->admin_transactions_cleared_at = now();
        $gs->save();
        \Cache::forget('GeneralSetting');
        return responseSuccess('cleared', ['Master Admin transaction history cleared (logs hidden)']);
    }

    public function clearOrders()
    {
        $gs = gs();
        $gs->admin_orders_cleared_at = now();
        $gs->save();
        \Cache::forget('GeneralSetting');
        return responseSuccess('cleared', ['Master Admin orders history cleared (logs hidden)']);
    }

    public function clearDeposits()
    {
        $gs = gs();
        $gs->admin_deposits_cleared_at = now();
        $gs->save();
        \Cache::forget('GeneralSetting');
        return responseSuccess('cleared', ['Master Admin deposit history cleared (logs hidden)']);
    }

    public function clearWithdrawals()
    {
        $gs = gs();
        $gs->admin_withdrawals_cleared_at = now();
        $gs->save();
        \Cache::forget('GeneralSetting');
        return responseSuccess('cleared', ['Master Admin withdrawal history cleared (logs hidden)']);
    }

public function clearLedger()
{
    $gs = gs();
    $gs->admin_ledger_cleared_at = now();
    $gs->save();
    \Cache::forget('GeneralSetting');
    return responseSuccess('cleared', ['Master Admin account ledger history cleared (logs hidden)']);
}
    public function getEmailSettings()
    {
        $gs = gs();
        return responseSuccess('email_settings', ['Email settings retrieved'], [
            'email_from' => $gs->email_from,
            'mail_config' => $gs->mail_config,
        ]);
    }

    public function updateEmailSettings(Request $request)
    {
        if (!$this->checkPermission('manage_settings')) {
            return responseError('permission_denied', ['You do not have permission to update system settings.']);
        }
        $request->validate([
            'email_from' => 'required|email',
            'mail_method' => 'required|in:php,smtp',
            'smtp_host' => 'required_if:mail_method,smtp',
            'smtp_port' => 'required_if:mail_method,smtp',
            'smtp_username' => 'required_if:mail_method,smtp',
            'smtp_password' => 'required_if:mail_method,smtp',
            'smtp_encryption' => 'nullable|in:ssl,tls',
        ]);

        $gs = gs();
        $gs->email_from = $request->email_from;
        
        $mailConfig = [
            'name' => $request->mail_method,
        ];

        if ($request->mail_method === 'smtp') {
            $mailConfig['host'] = $request->smtp_host;
            $mailConfig['port'] = $request->smtp_port;
            $mailConfig['username'] = $request->smtp_username;
            $mailConfig['password'] = $request->smtp_password;
            $mailConfig['enc'] = $request->smtp_encryption;
        }

        $gs->mail_config = $mailConfig;
        $gs->save();

        return responseSuccess('email_settings_updated', ['Email settings updated successfully']);
    }

    public function getWithdrawalSettings()
    {
        $method = \App\Models\WithdrawMethod::active()->first();
        if (!$method) {
            return responseError('not_found', ['No active withdrawal method found. Please create one first in Admin -> Withdraw -> Methods.']);
        }

        return responseSuccess('withdrawal_settings', ['Withdrawal settings retrieved'], [
            'method_id' => $method->id,
            'user_bank' => [
                'min_limit' => (float) $method->min_limit,
                'max_limit' => (float) $method->max_limit,
                'fixed_charge' => (float) ($method->user_bank_fixed_charge ?? $method->fixed_charge),
                'percent_charge' => (float) ($method->user_bank_percent_charge ?? $method->percent_charge),
            ],
            'user_upi' => [
                'min_limit' => (float) ($method->user_upi_min_limit ?? $method->min_limit),
                'max_limit' => (float) ($method->user_upi_max_limit ?? $method->max_limit),
                'fixed_charge' => (float) ($method->user_upi_fixed_charge ?? 0),
                'percent_charge' => (float) ($method->user_upi_percent_charge ?? 0),
            ],
            'affiliate' => [
                'min_limit' => (float) ($method->affiliate_min_limit ?? 10000),
                'max_limit' => (float) ($method->affiliate_max_limit ?? 1000000),
                'fixed_charge' => (float) ($method->affiliate_fixed_charge ?? 0),
                'percent_charge' => (float) ($method->affiliate_percent_charge ?? 0),
            ]
        ]);
    }

    public function updateWithdrawalSettings(Request $request)
    {
        if (!$this->checkPermission('manage_settings')) {
            return responseError('permission_denied', ['You do not have permission to update system settings.']);
        }
        $request->validate([
            'user_bank_fixed_charge' => 'required|numeric|min:0',
            'user_bank_percent_charge' => 'required|numeric|min:0|max:100',
            'user_bank_min_limit' => 'required|numeric|min:0',
            'user_bank_max_limit' => 'required|numeric|gt:user_bank_min_limit',
            
            'user_upi_fixed_charge' => 'required|numeric|min:0',
            'user_upi_percent_charge' => 'required|numeric|min:0|max:100',
            'user_upi_min_limit' => 'required|numeric|min:0',
            'user_upi_max_limit' => 'required|numeric|gt:user_upi_min_limit',

            'affiliate_fixed_charge' => 'required|numeric|min:0',
            'affiliate_percent_charge' => 'required|numeric|min:0|max:100',
            'affiliate_min_limit' => 'required|numeric|min:0',
            'affiliate_max_limit' => 'required|numeric|gt:affiliate_min_limit',
        ]);

        $method = \App\Models\WithdrawMethod::active()->first();
        if (!$method) {
            return responseError('not_found', ['No active withdrawal method found.']);
        }

        $method->min_limit = $request->user_bank_min_limit;
        $method->max_limit = $request->user_bank_max_limit;
        $method->user_bank_fixed_charge = $request->user_bank_fixed_charge;
        $method->user_bank_percent_charge = $request->user_bank_percent_charge;

        $method->user_upi_min_limit = $request->user_upi_min_limit;
        $method->user_upi_max_limit = $request->user_upi_max_limit;
        $method->user_upi_fixed_charge = $request->user_upi_fixed_charge;
        $method->user_upi_percent_charge = $request->user_upi_percent_charge;

        $method->affiliate_min_limit = $request->affiliate_min_limit;
        $method->affiliate_max_limit = $request->affiliate_max_limit;
        $method->affiliate_fixed_charge = $request->affiliate_fixed_charge;
        $method->affiliate_percent_charge = $request->affiliate_percent_charge;
        
        $method->save();

        return responseSuccess('withdrawal_settings_updated', ['Withdrawal settings updated successfully']);
    }
}

