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
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller {

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
        }

        $users = $query->paginate($perPage);

        // Get all user IDs for this page
        $userIds = $users->pluck('id')->toArray();

        // Calculate total deposits for all users in a single query (optimization)
        $totalDeposits = Deposit::whereIn('user_id', $userIds)
            ->successful()
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(amount) as total_deposit')
            ->pluck('total_deposit', 'user_id')
            ->toArray();

        $users->getCollection()->transform(function ($user) use ($totalDeposits) {
            // Get total deposit from pre-calculated array
            $totalDeposit = $totalDeposits[$user->id] ?? 0;

            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'mobile' => $user->mobile ?? '',
                'password' => $user->password, // Include password (will be shown as masked in frontend)
                'balance' => $user->balance,
                'total_deposit' => $totalDeposit,
                'status' => $user->status == 1 ? 'active' : 'banned',
                'is_agent' => (bool) ($user->is_agent ?? false),
                'ev' => $user->ev,
                'sv' => $user->sv,
                'kv' => $user->kv,
                'kyc_data' => $this->normalizeKycData($user->kyc_data ?? null),
                'kyc_rejection_reason' => $user->kyc_rejection_reason ?? null,
                'referral_code' => $user->referral_code ?? '',
                'referred_by' => $user->ref_by ?? null,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                'last_login' => $user->login_at ?? null,
                'bank_details' => [
                    'account_holder_name' => $user->account_holder_name ?? '',
                    'account_number' => $user->account_number ?? '',
                    'ifsc_code' => $user->ifsc_code ?? '',
                    'bank_name' => $user->bank_name ?? '',
                    'bank_registered_no' => $user->bank_registered_no ?? '',
                    'branch_name' => $user->branch_name ?? '',
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
     * Create a new user from Master Admin panel (API).
     * ID is auto-generated by DB (auto-increment; series starts from ADS15000 requirement).
     */
    public function createUser(Request $request)
    {
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
            'mobile' => 'required|string|max:20|unique:users,mobile',
            'state' => 'required|string|max:255',
            'password' => 'required|string|min:6|max:255',
            // Sponsor ID (ADS ID / Username)
            'sponsor_id' => 'nullable|string|max:255',
            // Optional: activate a course package (course plan) for this user
            'course_plan_id' => 'nullable|integer|min:0',
            // Legacy keys (ignored in Phase 2 UI)
            'ref_mode' => 'nullable|in:normal,referral',
            'ref' => 'nullable|string|max:255',
            'package_id' => 'nullable|integer|in:0,1,2,3,4,5',
        ]);

        // Optional course plan to activate
        $coursePlanId = (int) $request->input('course_plan_id', 0);
        $coursePlan = null;
        if ($coursePlanId > 0) {
            $coursePlan = CoursePlan::where('id', $coursePlanId)->where('status', 1)->first();
            if (!$coursePlan) {
                return responseError('course_plan_not_found', ['Course package not found or inactive.']);
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
        $refMode = $request->input('ref_mode', 'referral'); // default to sponsor provided
        $sponsorInput = trim((string) $request->input('sponsor_id', ''));
        $legacyRef = trim((string) $request->input('ref', ''));
        $ref = $sponsorInput !== '' ? $sponsorInput : $legacyRef;
        $refBy = 0;
        if ($refMode === 'referral' && $ref !== '') {
            // Support ADS{id} or username
            if (preg_match('/^ADS(\d+)$/i', $ref, $m)) {
                $refBy = (int) $m[1];
            } else {
                $refBy = (int) (User::where('username', $ref)->value('id') ?? 0);
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
        try {
            $user = User::findOrFail($id);
            $user->status = 1;
            $user->save();
            return responseSuccess('user_unbanned', ['User unbanned successfully']);
        } catch (\Exception $e) {
            return responseError('error', ['Failed to unban user']);
        }
    }

    /**
     * Update user basic info (Admin API)
     * Fields: Name, Email, Number (mobile), State
     */
    public function updateUserBasic(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'mobile' => 'required|string|max:20|unique:users,mobile,' . $id,
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
        $request->validate([
            'password' => 'required|string|min:6|max:255',
        ]);

        $user = User::findOrFail($id);
        $user->password = (string) $request->password;
        $user->save();

        return responseSuccess('password_reset', ['Password reset successfully']);
    }

    /**
     * Mark/unmark a user as Agent (Admin API)
     */
    public function setUserAgent(Request $request, $id)
    {
        $request->validate([
            'is_agent' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        if (Schema::hasColumn('users', 'is_agent')) {
            $user->is_agent = (bool) $request->is_agent;
            $user->save();
        }

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
            ],
        ]);
    }

    /**
     * Get per-agent commission settings (Admin API)
     */
    public function getAgentCommissionSettings($id)
    {
        $user = User::findOrFail($id);
        $row = \DB::table('agent_commission_settings')->where('user_id', $user->id)->first();

        return responseSuccess('agent_commissions', ['Agent commission settings retrieved'], [
            'user_id' => $user->id,
            'is_agent' => (bool) ($user->is_agent ?? false),
            'settings' => $row ? (array) $row : null,
        ]);
    }

    /**
     * Update per-agent commission settings (Admin API)
     */
    public function updateAgentCommissionSettings(Request $request, $id)
    {
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
        ]);

        // Create row if missing
        $existing = \DB::table('agent_commission_settings')->where('user_id', $user->id)->first();
        if (!$existing) {
            \DB::table('agent_commission_settings')->insert([
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $payload = [
            'updated_at' => now(),
        ];

        $fields = [
            'registration_enabled','registration_mode','registration_value',
            'kyc_enabled','kyc_mode','kyc_value',
            'withdraw_fee_enabled','withdraw_fee_mode','withdraw_fee_value',
            'upgrade_enabled','upgrade_mode','upgrade_value',
            'partner_override_enabled','partner_override_percent',
        ];
        foreach ($fields as $f) {
            if ($request->has($f)) {
                $payload[$f] = $request->input($f);
            }
        }

        \DB::table('agent_commission_settings')->where('user_id', $user->id)->update($payload);
        $row = \DB::table('agent_commission_settings')->where('user_id', $user->id)->first();

        return responseSuccess('agent_commissions_updated', ['Agent commission settings updated'], [
            'user_id' => $user->id,
            'settings' => $row ? (array) $row : null,
        ]);
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
        $request->validate([
            'sponsor_id' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $ref = trim((string) $request->input('sponsor_id', ''));

        $refBy = 0;
        if ($ref !== '') {
            if (preg_match('/^ADS(\d+)$/i', $ref, $m)) {
                $refBy = (int) $m[1];
            } else {
                $refBy = (int) (User::where('username', $ref)->value('id') ?? 0);
            }
        }

        if ($refBy === $user->id) {
            return responseError('invalid_sponsor', ['User cannot be their own sponsor.']);
        }
        if ($refBy > 0 && !User::where('id', $refBy)->exists()) {
            return responseError('invalid_sponsor', ['Sponsor not found.']);
        }

        $user->ref_by = $refBy;
        $user->save();

        return responseSuccess('sponsor_updated', ['Sponsor updated successfully'], [
            'referred_by' => $user->ref_by ?: null,
        ]);
    }

    /**
     * Approve user KYC (API)
     */
    public function approveKyc($id)
    {
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
        $request->validate(['reason' => 'required|string|max:1000']);
        try {
            $user = User::findOrFail($id);
            $user->kv = Status::KYC_UNVERIFIED;
            $user->kyc_rejection_reason = $request->reason;
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
        try {
            $user = User::findOrFail($id);
            $user->kv = Status::KYC_UNVERIFIED;
            $user->kyc_rejection_reason = null;
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
        try {
            $user = User::findOrFail($id);
            
            $validated = $request->validate([
                'account_holder_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:255',
                'ifsc_code' => 'required|string|max:20',
                'bank_name' => 'required|string|max:255',
                'bank_registered_no' => 'nullable|string|max:255',
                'branch_name' => 'nullable|string|max:255',
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

        $query = Deposit::with('user')->orderBy('id', 'desc');

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
        } elseif ($status === 'approved') {
            $query->where('method_code', '>=', 1000)
                  ->where('method_code', '<', 5000)
                  ->where('status', Status::PAYMENT_SUCCESS);
        } elseif ($status === 'successful') {
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
            // Ignore invalid date formats
        }

        $deposits = $query->paginate($perPage);

        $deposits->getCollection()->transform(function ($deposit) {
            $statusText = 'Unknown';
            $statusClass = 'secondary';
            
            if ($deposit->status == Status::PAYMENT_PENDING) {
                $statusText = 'Pending';
                $statusClass = 'warning';
            } elseif ($deposit->status == Status::PAYMENT_SUCCESS) {
                if ($deposit->method_code >= 1000 && $deposit->method_code < 5000) {
                    $statusText = 'Approved';
                } else {
                    $statusText = 'Successful';
                }
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
                'created_at' => $deposit->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $deposit->created_at->diffForHumans(),
                'updated_at' => $deposit->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        // Calculate summary (respect date range filter; ignore pagination)
        $summaryQuery = Deposit::query();
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

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'successful' => (clone $summaryQuery)->where('status', Status::PAYMENT_SUCCESS)->sum('amount'),
            'pending' => (clone $summaryQuery)->where('status', Status::PAYMENT_PENDING)->sum('amount'),
            'rejected' => (clone $summaryQuery)->where('status', Status::PAYMENT_REJECT)->sum('amount'),
            'initiated' => (clone $summaryQuery)->where('status', Status::PAYMENT_INITIATE)->sum('amount'),
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
        $remarkLabels = [
            'registration_fee' => 'Registration Fee',
            'kyc_fee' => 'KYC Fee',
            'package_upgrade_gateway' => 'Package Purchase',
            'ad_plan_purchase' => 'Ad Plan Purchase',
            'partner_program_gateway' => 'Partner Program',
            'course_plan_purchase_gateway' => 'Course Plan Purchase',
            'deposit' => 'Deposit',
            'campaign_payment' => 'Campaign Payment',
        ];

        $pending = (int) Status::PAYMENT_PENDING;
        $success = (int) Status::PAYMENT_SUCCESS;
        $reject = (int) Status::PAYMENT_REJECT;
        $init = (int) Status::PAYMENT_INITIATE;

        // Gateway deposits: method_code 1000-4999
        $depositQ = DB::table('deposits as d')
            ->leftJoin('users as u', 'u.id', '=', 'd.user_id')
            ->where('d.method_code', '>=', 1000)
            ->where('d.method_code', '<', 5000)
            ->select([
                DB::raw("'deposit' as source"),
                DB::raw('d.id as source_id'),
                DB::raw('d.trx as trx'),
                DB::raw('d.amount as amount'),
                DB::raw('COALESCE(d.charge, 0) as charge'),
                DB::raw('(d.amount - COALESCE(d.charge, 0)) as after_charge'),
                DB::raw("'WatchPay' as method_name"),
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
                DB::raw("'Deposit' as order_type"),
                DB::raw("NULL as remark"),
                DB::raw('d.user_id as user_id'),
                DB::raw('u.username as username'),
                DB::raw('u.email as email'),
                DB::raw('u.firstname as firstname'),
                DB::raw('u.lastname as lastname'),
                DB::raw('d.created_at as created_at'),
                DB::raw("CASE WHEN d.status = {$pending} THEN 1 ELSE 0 END as approvable"),
            ]);

        // Gateway payments: transactions table (successful gateway payments)
        $trxCase = "CASE t.remark\n";
        foreach ($remarkLabels as $rk => $lbl) {
            $trxCase .= "  WHEN '{$rk}' THEN '{$lbl}'\n";
        }
        $trxCase .= "  ELSE t.remark END";

        $trxQ = DB::table('transactions as t')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->where('t.trx_type', '-') // outgoing payment record
            ->whereIn('t.remark', $allowedRemarks)
            ->select([
                DB::raw("'payment' as source"),
                DB::raw('t.id as source_id'),
                DB::raw('t.trx as trx'),
                DB::raw('t.amount as amount'),
                DB::raw('COALESCE(t.charge, 0) as charge'),
                DB::raw('t.amount as after_charge'),
                DB::raw("'WatchPay' as method_name"),
                DB::raw("{$success} as status"),
                DB::raw("'Successful' as status_text"),
                DB::raw("'success' as status_class"),
                DB::raw("{$trxCase} as order_type"),
                DB::raw('t.remark as remark'),
                DB::raw('t.user_id as user_id'),
                DB::raw('u.username as username'),
                DB::raw('u.email as email'),
                DB::raw('u.firstname as firstname'),
                DB::raw('u.lastname as lastname'),
                DB::raw('t.created_at as created_at'),
                DB::raw('0 as approvable'),
            ]);

        // Union + wrap so we can order/paginate
        $union = $depositQ->unionAll($trxQ);
        $base = DB::query()->fromSub($union, 'x');

        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->where('x.trx', 'like', "%{$search}%")
                    ->orWhere('x.username', 'like', "%{$search}%")
                    ->orWhere('x.email', 'like', "%{$search}%");
            });
        }
        if ($userId) {
            $base->where('x.user_id', (int) $userId);
        }
        if ($status === 'pending') {
            $base->where('x.source', 'deposit')->where('x.status', $pending);
        } elseif ($status === 'approved' || $status === 'successful') {
            // approved deposits + all gateway payments
            $base->where(function ($q) use ($success) {
                $q->where(function ($q2) use ($success) {
                    $q2->where('x.source', 'deposit')->where('x.status', $success);
                })->orWhere('x.source', 'payment');
            });
        } elseif ($status === 'rejected') {
            $base->where('x.source', 'deposit')->where('x.status', $reject);
        } elseif ($status === 'initiated') {
            $base->where('x.source', 'deposit')->where('x.status', $init);
        }

        // Date filter (inclusive)
        try {
            if ($startDate) {
                $base->whereDate('x.created_at', '>=', Carbon::parse($startDate)->toDateString());
            }
            if ($endDate) {
                $base->whereDate('x.created_at', '<=', Carbon::parse($endDate)->toDateString());
            }
        } catch (\Throwable $e) {}

        $page = $base->orderByDesc('x.created_at')->paginate($perPage);

        $items = collect($page->items())->map(function ($r) {
            return [
                'id' => (int) ($r->source_id ?? 0),
                'source' => (string) ($r->source ?? ''),
                'source_id' => (int) ($r->source_id ?? 0),
                'trx' => (string) ($r->trx ?? ''),
                'amount' => (float) ($r->amount ?? 0),
                'charge' => (float) ($r->charge ?? 0),
                'after_charge' => (float) ($r->after_charge ?? 0),
                'method_name' => (string) ($r->method_name ?? 'Gateway'),
                'status' => (int) ($r->status ?? 0),
                'status_text' => (string) ($r->status_text ?? 'Unknown'),
                'status_class' => (string) ($r->status_class ?? 'secondary'),
                'order_type' => (string) ($r->order_type ?? ''),
                'remark' => $r->remark,
                'approvable' => (int) ($r->approvable ?? 0) === 1,
                'user_id' => (int) ($r->user_id ?? 0),
                'user' => ($r->user_id ?? null) ? [
                    'id' => (int) ($r->user_id ?? 0),
                    'username' => (string) ($r->username ?? ''),
                    'email' => (string) ($r->email ?? ''),
                    'firstname' => (string) ($r->firstname ?? ''),
                    'lastname' => (string) ($r->lastname ?? ''),
                ] : null,
                'created_at' => $r->created_at ? (string) $r->created_at : null,
            ];
        })->values()->all();

        // Summary from same filters (but without pagination)
        $summaryBase = DB::query()->fromSub($union, 'x');
        if ($search) {
            $summaryBase->where(function ($q) use ($search) {
                $q->where('x.trx', 'like', "%{$search}%")
                    ->orWhere('x.username', 'like', "%{$search}%")
                    ->orWhere('x.email', 'like', "%{$search}%");
            });
        }
        if ($userId) $summaryBase->where('x.user_id', (int) $userId);
        if ($status === 'pending') {
            $summaryBase->where('x.source', 'deposit')->where('x.status', $pending);
        } elseif ($status === 'approved' || $status === 'successful') {
            $summaryBase->where(function ($q) use ($success) {
                $q->where(function ($q2) use ($success) {
                    $q2->where('x.source', 'deposit')->where('x.status', $success);
                })->orWhere('x.source', 'payment');
            });
        } elseif ($status === 'rejected') {
            $summaryBase->where('x.source', 'deposit')->where('x.status', $reject);
        } elseif ($status === 'initiated') {
            $summaryBase->where('x.source', 'deposit')->where('x.status', $init);
        }
        try {
            if ($startDate) $summaryBase->whereDate('x.created_at', '>=', Carbon::parse($startDate)->toDateString());
            if ($endDate) $summaryBase->whereDate('x.created_at', '<=', Carbon::parse($endDate)->toDateString());
        } catch (\Throwable $e) {}

        $summary = [
            'total' => (clone $summaryBase)->count(),
            'successful' => (clone $summaryBase)->where('x.status', $success)->sum('x.amount'),
            'pending' => (clone $summaryBase)->where('x.source', 'deposit')->where('x.status', $pending)->sum('x.amount'),
            'rejected' => (clone $summaryBase)->where('x.source', 'deposit')->where('x.status', $reject)->sum('x.amount'),
            'initiated' => (clone $summaryBase)->where('x.source', 'deposit')->where('x.status', $init)->sum('x.amount'),
        ];

        return responseSuccess('all_gateway_orders', ['Gateway orders retrieved successfully'], [
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
            'kyc_fee' => 'KYC Fee',
            'package_upgrade_gateway' => 'Package Purchase',
            'ad_plan_purchase' => 'Ad Plan Purchase',
            'partner_program_gateway' => 'Partner Program',
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
            'total' => (clone $summaryQuery)->count(),
            'processing' => (clone $summaryQuery)->where('status', Status::PAYMENT_PENDING)->sum('amount'),
            'success' => (clone $summaryQuery)->where('status', Status::PAYMENT_SUCCESS)->sum('amount'),
            'rejected' => (clone $summaryQuery)->where('status', Status::PAYMENT_REJECT)->sum('amount'),
        ];

        return responseSuccess('withdrawals', ['Withdrawals retrieved successfully'], [
            'withdrawals' => $withdrawals->items(),
            'summary' => $summary,
            'total' => $withdrawals->total(),
            'per_page' => $withdrawals->perPage(),
            'current_page' => $withdrawals->currentPage(),
            'last_page' => $withdrawals->lastPage(),
        ]);
    }

    /**
     * Approve a withdrawal (Admin API)
     */
    public function approveWithdrawal(Request $request)
    {
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
     * Reject a withdrawal (Admin API) + refund to correct wallet.
     */
    public function rejectWithdrawal(Request $request)
    {
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
        // Check if API request
        if (request()->expectsJson() || request()->is('api/*')) {
            $widget = [
                'total_users' => User::count(),
                'verified_users' => User::active()->count(),
                'email_unverified_users' => User::emailUnverified()->count(),
                'mobile_unverified_users' => User::mobileUnverified()->count(),
                'total_campaigns' => Campaign::count(),
                'pending_campaigns' => Campaign::pending()->count(),
                'approved_campaigns' => Campaign::approved()->count(),
                'rejected_campaigns' => Campaign::rejected()->count(),
                'total_courses' => \App\Models\Course::count(),
                'total_revenue' => Deposit::successful()->sum('amount') - Withdrawal::approved()->sum('amount'),
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
        // For API requests, use Sanctum auth
        if (request()->expectsJson() || request()->is('api/*')) {
            $user = auth('sanctum')->user();
            if (!$user || !($user instanceof \App\Models\Admin)) {
                return responseError('unauthorized', ['Unauthorized']);
            }

            return responseSuccess('admin_user', ['Admin user retrieved successfully'], [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ]);
        }

        // For Blade requests
        $admin = auth('admin')->user();
        return responseSuccess('admin_user', ['Admin user retrieved successfully'], [
            'id' => $admin->id,
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
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
        $deposits = Deposit::successful()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $withdrawals = Withdrawal::approved()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
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

        $plusTransactions = Transaction::where('trx_type', '+')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $minusTransactions = Transaction::where('trx_type', '-')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
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

}
