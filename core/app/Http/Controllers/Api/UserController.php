<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Lib\AgentCommission;
use App\Models\Campaign;
use App\Models\Conversion;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Frontend;
use App\Models\Form;
use App\Lib\FormProcessor;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        // Only these credit transactions count as "income" in dashboard totals.
        // (Exclude refunds/recredits like withdraw_reject, and admin balance adjustments.)
        $incomeRemarks = [
            'ad_view_reward',
            'referral_commission',
            'affiliate_commission',
            'downline_commission',
        ];

        // Calculate widget data
        $widget = [
            'balance' => $user->balance ?? 0,
            'total_earning' => Conversion::where('user_id', $user->id)
                ->where('is_paid', Status::PAID)
                ->sum('user_payout'),
            'ads_income' => Transaction::where('user_id', $user->id)
                ->where('remark', 'ad_view_reward')
                ->where('trx_type', '+')
                ->sum('amount'),
            'total_withdraw' => Withdrawal::where('user_id', $user->id)
                ->where('status', Status::PAYMENT_SUCCESS)
                ->sum('amount'),
        ];

        // Get latest transactions
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($trx) {
                return [
                    'id' => $trx->id,
                    'trx' => $trx->trx,
                    'trx_type' => $trx->trx_type,
                    'amount' => $trx->amount,
                    'post_balance' => $trx->post_balance,
                    'details' => $trx->details,
                    'remark' => $trx->remark,
                    'created_at' => $trx->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $trx->created_at->diffForHumans(),
                ];
            });

        // Get suggested campaigns
        $campaigns = Campaign::running()
            ->whereHas('category', function ($query) {
                $query->active();
            })
            ->take(8)
            ->latest()
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'slug' => $campaign->slug,
                    'image' => getImage(getFilePath('campaign') . '/' . 'thumb_' . $campaign->image, getFileThumb('campaign')),
                    'payout_per_conversion' => $campaign->payout_per_conversion,
                ];
            });

        // Get KYC content
        $kyc = Frontend::where('data_keys', 'kyc.content')->first();
        $kycContent = $kyc ? $kyc->data_values : null;

        // Calculate earnings for different periods
        $today = now()->startOfDay();
        $last7Days = now()->subDays(7)->startOfDay();
        $last30Days = now()->subDays(30)->startOfDay();

        // Unified stats query for conversions
    $convStats = Conversion::where('user_id', $user->id)
        ->where('is_paid', Status::PAID)
        ->selectRaw("
            SUM(CASE WHEN created_at >= ? THEN user_payout ELSE 0 END) as today,
            SUM(CASE WHEN created_at >= ? THEN user_payout ELSE 0 END) as last7,
            SUM(CASE WHEN created_at >= ? THEN user_payout ELSE 0 END) as last30,
            SUM(user_payout) as total
        ", [$today, $last7Days, $last30Days])
        ->first();

    // Unified stats query for transactions (Top Cards: Ads only as per request)
    $trxStats = Transaction::where('user_id', $user->id)
        ->where('trx_type', '+')
        ->where('remark', 'ad_view_reward')
        ->selectRaw("
            SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as today,
            SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as last7,
            SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as last30,
            SUM(amount) as total
        ", [$today, $last7Days, $last30Days])
        ->first();

    // Ad view reward sum (specifically for widget logic)
    $adsIncome = (float) ($trxStats->total ?? 0);

    // Affiliate wallet sum (all time) - This will show in the "Affiliate Income" card
    $affiliateRemarks = [
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

    $affiliateStats = Transaction::where('user_id', $user->id)
        ->where('wallet', 'affiliate')
        ->where('trx_type', '+')
        ->whereIn('remark', $affiliateRemarks)
        ->selectRaw("
            SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as today,
            SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as last7,
            SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as last30,
            SUM(amount) as total
        ", [$today, $last7Days, $last30Days])
        ->first();

    // Calculate widget data
    $widget = [
        'balance' => (float) ($user->balance ?? 0),
        'affiliate_balance' => (float) ($user->affiliate_balance ?? 0),
        'total_earning' => (float) ($affiliateStats->total ?? 0), // Fetch total affiliate income here
        'ads_income' => $adsIncome,
        'total_withdraw' => (float) Withdrawal::where('user_id', $user->id)
            ->where('status', Status::PAYMENT_SUCCESS)
            ->sum('amount'),
    ];

    // Earnings shows ONLY Ads + Conversions (Work income) as per user request
    $earnings = [
        'today' => (float) ($trxStats->today ?? 0) + (float) ($convStats->today ?? 0),
        'last7Days' => (float) ($trxStats->last7 ?? 0) + (float) ($convStats->last7 ?? 0),
        'last30Days' => (float) ($trxStats->last30 ?? 0) + (float) ($convStats->last30 ?? 0),
        'total' => (float) ($trxStats->total ?? 0) + (float) ($convStats->total ?? 0),
    ];

        // Get general settings for currency
        $general = gs();

        return responseSuccess('dashboard_data', ['Dashboard data retrieved successfully'], [
            'widget' => $widget,
            'earnings' => $earnings,
            'transactions' => $transactions,
            'campaigns' => $campaigns,
            'kyc_content' => $kycContent,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'kv' => $user->kv,
                'kyc_rejection_reason' => $user->kyc_rejection_reason,
                'image' => $user->image
                    ? getImage(getFilePath('userProfile') . '/' . $user->image, getFileSize('userProfile'))
                    : '/assets/images/default.png',
            ],
        ]);
    }

    public function conversionLog(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');

        $query = Conversion::where('user_id', $user->id)->with('campaign', 'user');

        if ($search) {
            $query->whereHas('campaign', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $conversions = $query->latest()->paginate(getPaginate());
        
        $conversionsData = $conversions->map(function ($conversion) {
            return [
                'id' => $conversion->id,
                'campaign' => [
                    'title' => $conversion->campaign->title ?? '-',
                ],
                'admin_commission' => $conversion->admin_commission,
                'user_payout' => $conversion->user_payout,
                'is_paid' => $conversion->is_paid,
                'ip_address' => $conversion->ip_address,
                'tracking_type' => $conversion->tracking_type,
                'user_agent' => $conversion->user_agent,
                'details' => $conversion->details,
                'created_at' => $conversion->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $conversion->created_at->diffForHumans(),
                'user' => [
                    'username' => $conversion->user->username ?? '-',
                ],
            ];
        });

        return responseSuccess('conversion_log', ['Conversion log retrieved successfully'], [
            'data' => $conversionsData,
            'pagination' => [
                'current_page' => $conversions->currentPage(),
                'last_page' => $conversions->lastPage(),
                'total' => $conversions->total(),
                'per_page' => $conversions->perPage(),
            ],
        ]);
    }

    public function accountKYC()
    {
        $user = auth()->user();
        $general = gs();
        // Phase 1 requirement: KYC fee must be ₹990 (payment-first via gateway)
        // If setting is missing/invalid/0, force 990 to avoid bypass.
        $kycFee = (float) ($general->kyc_fee ?? 990);
        if ($kycFee <= 0) {
            $kycFee = 990;
        }

        // Check if KYC payment has been made (gateway-only).
        // IMPORTANT: Do NOT infer paid status from legacy `transactions.remark = kyc_fee`.
        // There is a migration that deletes those rows because they were wallet-style and not a reliable gateway proof.
        $hasPaidKYCFee = (bool) ($user->has_paid_kyc_fee ?? false);
        $hasProof = !empty($user->kyc_fee_trx) || !empty($user->kyc_fee_paid_at);
        if ($hasPaidKYCFee && !$hasProof) {
            // Auto-correct any incorrectly set flags (e.g., legacy auto-upgrade without proof)
            $user->has_paid_kyc_fee = false;
            $user->save();
            $hasPaidKYCFee = false;
        }

        $responseData = [
            'personal_details' => [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'country_name' => $user->country_name,
            ],
            'bank_details' => [
                'account_holder_name' => $user->account_holder_name ?? '',
                'account_number' => $user->account_number ?? '',
                'ifsc_code' => $user->ifsc_code ?? '',
                'bank_name' => $user->bank_name ?? '',
                'bank_registered_no' => $user->bank_registered_no ?? '',
                'branch_name' => $user->branch_name ?? '',
                'upi_id' => $user->upi_id ?? '',
            ],
            'kyc_fee' => $kycFee,
            'kyc_status' => $user->kv ?? 0,
            'kyc_rejection_reason' => $user->kyc_rejection_reason ?? '',
            'balance' => $user->balance ?? 0,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'has_paid_kyc_fee' => $hasPaidKYCFee,
            'kyc_fee_trx' => $user->kyc_fee_trx ?? null,
            'kyc_fee_paid_at' => ($user->kyc_fee_paid_at instanceof \DateTime) ? $user->kyc_fee_paid_at->format('Y-m-d H:i:s') : $user->kyc_fee_paid_at,
        ];

        if ($user->id == 15000) {
            \Log::warning('DEBUG KYC RESPONSE FOR 15000:', $responseData);
        }

        return responseSuccess('account_kyc', ['Account KYC data retrieved successfully'], $responseData);
    }

    public function updateBankDetails(Request $request)
    {
        $user = auth()->user();

        // Once KYC is verified, user must reset old KYC before editing bank details
        if ($user->kv == Status::KYC_VERIFIED) {
            return responseError('kyc_verified_locked', ['KYC is verified. You cannot edit details unless you delete old KYC and resubmit.']);
        }
        
        $validator = Validator::make($request->all(), [
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:20',
            'bank_name' => 'required|string|max:255',
            'bank_registered_no' => 'required|string|max:255', // Enforce required as per frontend
            'upi_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $user->account_holder_name = $request->account_holder_name;
        $user->account_number = $request->account_number;
        $user->ifsc_code = $request->ifsc_code;
        $user->bank_name = $request->bank_name;
        $user->bank_registered_no = $request->bank_registered_no;
        $user->upi_id = $request->upi_id;
        
        $saved = $user->save();

        if ($saved) {
            return responseSuccess('bank_details_updated', ['Bank details updated successfully']);
        }

        return responseError('save_failed', ['Could not save details in database']);
    }

    public function kycPayment(Request $request)
    {
        $user = auth()->user();
        $general = gs();
        $kycFee = (float) ($general->kyc_fee ?? 990);
        if ($kycFee <= 0) {
            $kycFee = 990;
        }

        $gateway = $request->input('gateway', 'watchpay');
        if (!in_array($gateway, ['watchpay', 'simplypay', 'rupeerush'])) {
            $gateway = 'watchpay';
        }

        // Check if payment already made (gateway-only proof; NOT wallet balance payment)
        $hasPaid = (bool) ($user->has_paid_kyc_fee ?? false) && (!empty($user->kyc_fee_trx) || !empty($user->kyc_fee_paid_at));
        if ((bool) ($user->has_paid_kyc_fee ?? false) && !$hasPaid) {
            // Fix any bad legacy flags so user can pay again properly
            $user->has_paid_kyc_fee = false;
            $user->save();
        }
        if ($hasPaid) {
            return responseError('payment_already_made', ['KYC payment has already been made. Please proceed with KYC submission.']);
        }

        $trx = getTrx();

        // 🟢 Create a real Deposit record so it shows in "All Orders" immediately (Initiated)
        $gate = \App\Models\Gateway::where('alias', $gateway)->first();
        $deposit = new \App\Models\Deposit();
        $deposit->user_id = $user->id;
        $deposit->method_code = $gate->code ?? 0;
        $deposit->method_currency = 'INR';
        $deposit->amount = $kycFee;
        $deposit->charge = 0;
        $deposit->rate = 1;
        $deposit->final_amount = $kycFee;
        $deposit->trx = $trx;
        $deposit->remark = 'kyc_fee';
        $deposit->status = Status::PAYMENT_INITIATE;
        $deposit->save();

        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $cacheKey = $cachePrefix . $trx;
        cache()->put($cacheKey, [
            'type' => 'kyc_fee',
            'user_id' => $user->id,
            'amount' => $kycFee,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $gw_param = 'watchpay_trx=';
        if ($gateway === 'simplypay') $gw_param = 'simplypay_trx=';
        if ($gateway === 'rupeerush') $gw_param = 'rupeerush_trx=';
        
        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/user/account-kyc?' . $gw_param . urlencode($trx);
        $notifyUrl = $base . '/ipn/' . $gateway;

        try {
            if ($gateway === 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => $kycFee,
                    'notifyUrl' => $notifyUrl,
                    'returnUrl' => $pageUrl,
                    'name' => $user->fullname ?: $user->username,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'attach' => 'KYC Verification Fee',
                ]);
                $paymentUrl = $sp['pay_link'];
            } elseif ($gateway === 'rupeerush') {
                $ap = \App\Lib\RupeeRushGateway::createPayment([
                    'outTradeNo' => $trx,
                    'totalAmount' => $kycFee,
                    'notifyUrl' => $notifyUrl,
                    'payViewUrl' => $pageUrl,
                    'payName' => $user->fullname ?: $user->username,
                    'email' => $user->email,
                    'payPhone' => $user->mobile,
                ]);
                $paymentUrl = $ap['pay_link'];
            } else {
                $wp = \App\Lib\WatchPayGateway::createWebPayment(
                    $trx,
                    (float) $kycFee,
                    'KYC Verification Fee',
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            \Log::error($gateway . ' KYC payment init failed', [
                'user_id' => $user->id,
                'amount' => $kycFee,
                'trx' => $trx,
                'error' => $e->getMessage(),
            ]);
            return responseError('payment_gateway_error', [$e->getMessage() ?: 'Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('payment_initiated', ['Payment gateway initialized'], [
            'payment_url' => $paymentUrl,
            'trx' => $trx,
            'amount' => $kycFee,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'gateway_name' => ($gateway === 'simplypay' ? 'SimplyPay' : ($gateway === 'rupeerush' ? 'RupeeRush' : 'WatchPay')),
        ]);
    }

    public function confirmKycPayment(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
            'gateway' => 'nullable|string|in:watchpay,simplypay,rupeerush',
        ]);

        $user = auth()->user();
        $general = gs();
        $kycFee = (float) ($general->kyc_fee ?? 990);
        if ($kycFee <= 0) {
            $kycFee = 990;
        }

        $trx = (string) $request->trx;
        $gateway = $request->input('gateway', 'watchpay');
        $cachePrefix = 'watchpay_payment_';
        if ($gateway === 'simplypay') $cachePrefix = 'simplypay_payment_';
        if ($gateway === 'rupeerush') $cachePrefix = 'rupeerush_payment_';
        
        $cacheKey = $cachePrefix . $trx;

        $session = cache()->get($cacheKey);
        if (!is_array($session) || ($session['type'] ?? '') !== 'kyc_fee' || (int)($session['user_id'] ?? 0) !== (int)$user->id) {
            return responseError('payment_not_found', ['Payment session not found. Please initiate payment again.']);
        }
        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['Payment not verified yet. Please complete payment and try again.']);
        }

        // Mark paid on user (do NOT deduct wallet balance)
        if (!(bool) ($user->has_paid_kyc_fee ?? false)) {
            $user->has_paid_kyc_fee = true;
            $user->kyc_fee_trx = $trx;
            $user->kyc_fee_paid_at = now();
            $user->save();
        }

        // Agent commission on KYC fee (only if sponsor is Agent and commission enabled)
        try {
            $agentId = (int) ($user->ref_by ?? 0);
            $paidAmount = (float) ($session['amount'] ?? $kycFee);
            if ($agentId > 0 && $paidAmount > 0) {
                AgentCommission::process(
                    $agentId,
                    'kyc',
                    $paidAmount,
                    $trx,
                    'Agent KYC commission from User#' . $user->id . ' (KYC fee) | Base: ₹' . $paidAmount
                );
            }
        } catch (\Throwable $e) {
            // never block KYC confirmation because of commission failure
        }

        return responseSuccess('kyc_payment_success', ['KYC payment verified successfully'], [
            'has_paid_kyc_fee' => true,
        ]);
    }

    public function userInfo()
    {
        $user = auth()->user();
        
        return responseSuccess('user_info', ['User info retrieved successfully'], [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullname' => $user->fullname,
            'mobile' => $user->mobile,
            'dial_code' => $user->dial_code,
            'balance' => $user->balance,
            'is_agent' => (bool) ($user->is_agent ?? false),
            'status' => $user->status,
            'ev' => $user->ev,
            'sv' => $user->sv,
            'kv' => $user->kv,
            'kyc_rejection_reason' => $user->kyc_rejection_reason,
            'image' => $user->image
                ? getImage(getFilePath('userProfile') . '/' . $user->image, getFileSize('userProfile'))
                : asset('assets/images/default.png'),
        ]);
    }

    public function submitProfile(Request $request)
    {
        $user = auth()->user();

        // If you want to keep other fields locked but allow image:
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => [
                    'nullable',
                    'file',
                    'max:2048', // 2MB
                    function ($attribute, $value, $fail) {
                        if (!$value) return;
                        $ext = strtolower($value->getClientOriginalExtension() ?? '');
                        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            $fail('Profile image must be a JPG or PNG file.');
                        }
                    },
                ],
            ]);

            try {
                $old = $user->image;
                $user->image = fileUploader(
                    $request->image,
                    getFilePath('userProfile'),
                    getFileSize('userProfile'),
                    $old
                );
                $user->save();
                return responseSuccess('profile_image_updated', ['Profile image updated successfully']);
            } catch (\Exception $e) {
                return responseError('image_upload_failed', ['Could not upload your image']);
            }
        }

        // Profile editing for other fields remains disabled.
        return response()->json([
            'status' => 'error',
            'remark' => 'profile_update_disabled',
            'message' => ['Profile text details can only be changed by contacting support.'],
        ], 403);
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', $passwordValidation],
        ]);

        $user = auth()->user();
        
        // Check plain password as per existing logic
        if ($user->password !== $request->current_password) {
            return responseError('password_mismatch', ['The password doesn\'t match!']);
        }

        $user->password = $request->password; // Store as plain text as per existing logic
        $user->save();

        return responseSuccess('password_changed', ['Password changed successfully']);
    }

    public function kycForm()
    {
        $user = auth()->user();
        
        // Check KYC status
        if ($user->kv == Status::KYC_PENDING) {
            return responseError('kyc_pending', ['Your KYC is under review']);
        }
        
        if ($user->kv == Status::KYC_VERIFIED) {
            return responseError('kyc_verified', ['You are already KYC verified']);
        }

        $form = Form::where('act', 'kyc')->first();
        
        if (!$form) {
            return responseError('kyc_form_not_found', ['KYC form not configured']);
        }

        // Convert form_data object to array format for Vue component
        $formData = $form->form_data ?? [];
        $fields = [];
        
        if (is_object($formData)) {
            foreach ($formData as $label => $data) {
                $fields[] = [
                    'name' => $data->name ?? '',
                    'label' => $data->label ?? $label,
                    'type' => $data->type ?? 'text',
                    'required' => ($data->is_required ?? 'optional') === 'required',
                    'options' => $data->options ?? [],
                    'extensions' => $data->extensions ?? '',
                    'accept' => $data->extensions ? '.' . str_replace(',', ',.', $data->extensions) : '',
                    'width' => $data->width ?? 12,
                    'instruction' => $data->instruction ?? '',
                ];
            }
        } elseif (is_array($formData)) {
            foreach ($formData as $label => $data) {
                $dataObj = is_object($data) ? $data : (object)$data;
                $fields[] = [
                    'name' => $dataObj->name ?? '',
                    'label' => $dataObj->label ?? $label,
                    'type' => $dataObj->type ?? 'text',
                    'required' => ($dataObj->is_required ?? 'optional') === 'required',
                    'options' => $dataObj->options ?? [],
                    'extensions' => $dataObj->extensions ?? '',
                    'accept' => $dataObj->extensions ? '.' . str_replace(',', ',.', $dataObj->extensions) : '',
                    'width' => $dataObj->width ?? 12,
                    'instruction' => $dataObj->instruction ?? '',
                ];
            }
        }

        $general = gs();
        $kycFee = $general->kyc_fee ?? 0;

        return responseSuccess('kyc_form', ['KYC form retrieved successfully'], [
            'fields' => $fields,
            'kyc_fee' => $kycFee,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function kycData()
    {
        $user = auth()->user();
        
        return responseSuccess('kyc_data', ['KYC data retrieved successfully'], [
            'kyc_data' => $user->kyc_data ?? [],
            'kyc_status' => $user->kv ?? 0,
        ]);
    }

    public function kycSubmit(Request $request)
    {
        $user = auth()->user();

        // Prevent updating KYC while verified or pending review
        if ($user->kv == Status::KYC_VERIFIED) {
            return responseError('kyc_verified_locked', ['KYC is verified. You cannot edit KYC unless you delete old KYC and resubmit.']);
        }
        if ($user->kv == Status::KYC_PENDING) {
            return responseError('kyc_pending', ['Your KYC is under review. Please wait for admin approval.']);
        }

        // Optional: enforce "watch 2 ads, earn ₹10k" before KYC – set to true to re-enable
        $requireNewUserOfferForKyc = (bool) (gs()->require_new_user_offer_for_kyc ?? false);
        if ($requireNewUserOfferForKyc) {
            $newUserAdsWatched = (int) ($user->new_user_ads_watched ?? 0);
            $requiredEarning = 10000;
            $newUserEarnings = Transaction::where('user_id', $user->id)
                ->where('remark', 'ad_view_reward')
                ->where('trx_type', '+')
                ->where('details', 'like', 'New user offer%')
                ->sum('amount');
            if ($newUserAdsWatched < 2 || $newUserEarnings < $requiredEarning) {
                return responseError('earn_10k_first', [
                    'Please complete the new user offer first. Watch 2 ads to earn ₹10,000, then you can submit KYC.'
                ]);
            }
        }

        // Enforce KYC fee payment BEFORE allowing submission (Phase 1: ₹990 payment-first)
        $general = gs();
        $kycFee = (float) ($general->kyc_fee ?? 990);
        if ($kycFee <= 0) {
            $kycFee = 990;
        }
        $hasPaidKYCFee = (bool) ($user->has_paid_kyc_fee ?? false) && (!empty($user->kyc_fee_trx) || !empty($user->kyc_fee_paid_at));
        if (!$hasPaidKYCFee) {
            return responseError('kyc_fee_required', [
                'Please pay the KYC verification fee (' . $general->cur_sym . $kycFee . ') before submitting your documents.',
            ]);
        }

        $form = Form::where('act', 'kyc')->first();
        if (!$form) {
            return responseError('kyc_form_not_found', ['KYC form not configured']);
        }

        $formData = $form->form_data;
        $processedFormData = [];
        
        // Fix: PHP automatically replaces spaces/dots in POST keys with underscores.
        // We must sync the form data labels to match what we receive in $request.
        foreach ($formData as $key => $data) {
            $dataObj = clone (object)$data; 
            // Standardize key: lowercase + underscores (matches frontend JS)
            $dataObj->label = strtolower(str_replace(' ', '_', $dataObj->label));
            $processedFormData[$key] = $dataObj;
        }
        $formData = $processedFormData;

        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        // Add specific regex for known fields (using underscored keys)
        foreach ($formData as $data) {
            // Case-insensitive check just to be safe
            $labelLower = strtolower($data->label);
            
            if ($labelLower == 'aadhaar_number') {
                $validationRule[$data->label][] = 'regex:/^[0-9]{12}$/';
            }
            if ($labelLower == 'pan_number') {
                $validationRule[$data->label][] = 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/';
            }
        }

        $request->validate($validationRule);

        // Remove old KYC files
        if (isset($user->kyc_data) && is_array($user->kyc_data)) {
            foreach ($user->kyc_data as $kycData) {
                 $type  = is_object($kycData) ? ($kycData->type ?? null) : ($kycData['type'] ?? null);
                 $value = is_object($kycData) ? ($kycData->value ?? null) : ($kycData['value'] ?? null);
                 if ($type == 'file' && $value) {
                    $oldPath = getFilePath('verify') . '/' . $value;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        }

        $userData = $formProcessor->processFormData($request, $formData);

        $user->kyc_data = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv = Status::KYC_PENDING;
        $user->save();

        return responseSuccess('kyc_submitted', ['KYC data submitted successfully. Your verification will be reviewed by admin soon.']);
    }

    /**
     * Reset (delete) existing bank + KYC data so user can resubmit after verification.
     */
    public function resetAccountKyc(Request $request)
    {
        $user = auth()->user();

        if ($user->kv != Status::KYC_VERIFIED) {
            return responseError('kyc_not_verified', ['Reset is only available after KYC is verified.']);
        }

        // Delete old KYC files (if any)
        $existing = $user->kyc_data ?? null;
        if ($existing) {
            foreach ($existing as $kycData) {
                $type  = is_object($kycData) ? ($kycData->type ?? null) : ($kycData['type'] ?? null);
                $value = is_object($kycData) ? ($kycData->value ?? null) : ($kycData['value'] ?? null);
                if ($type === 'file' && $value) {
                    $oldPath = getFilePath('verify') . '/' . $value;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        }

        // Clear KYC + bank info
        $user->kyc_data = null;
        $user->kyc_rejection_reason = null;
        $user->kv = Status::KYC_UNVERIFIED;

        $user->account_holder_name = null;
        $user->account_number = null;
        $user->ifsc_code = null;
        $user->bank_name = null;
        $user->bank_registered_no = null;
        $user->branch_name = null;
        $user->upi_id = null;

        $user->save();

        return responseSuccess('account_kyc_reset', ['Old KYC deleted. You can now add new bank details and submit KYC again.']);
    }

    public function transactions(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        $trxType = $request->get('trx_type');
        $remark = $request->get('remark');

        $query = Transaction::where('user_id', $user->id);

        if ($search) {
            $query->where('trx', 'like', "%{$search}%");
        }

        if ($trxType) {
            $query->where('trx_type', $trxType);
        }

        if ($remark) {
            $query->where('remark', $remark);
        }

        $transactionsPaginated = $query->latest()->paginate(getPaginate());
        
        $transactions = $transactionsPaginated->map(function ($trx) {
            return [
                'id' => $trx->id,
                'trx' => $trx->trx,
                'trx_type' => $trx->trx_type,
                'amount' => $trx->amount,
                'post_balance' => $trx->post_balance,
                'details' => $trx->details,
                'remark' => $trx->remark,
                'created_at' => $trx->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $trx->created_at->diffForHumans(),
            ];
        });

        // Get unique remarks for filter
        $remarks = Transaction::where('user_id', $user->id)
            ->distinct()
            ->pluck('remark')
            ->filter()
            ->map(function ($remark) {
                return ['remark' => $remark];
            })
            ->values();

        return responseSuccess('transactions', ['Transactions retrieved successfully'], [
            'data' => $transactions,
            'remarks' => $remarks,
            'pagination' => [
                'current_page' => $transactionsPaginated->currentPage(),
                'last_page' => $transactionsPaginated->lastPage(),
                'total' => $transactionsPaginated->total(),
            ],
        ]);
    }

    public function show2faForm()
    {
        $ga = new \App\Lib\GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);

        return responseSuccess('2fa_form', ['2FA form retrieved successfully'], [
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'is_enabled' => $user->ts == Status::ENABLE,
        ]);
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'key' => 'required',
            'code' => 'required',
        ]);

        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = Status::ENABLE;
            $user->save();
            return responseSuccess('2fa_enabled', ['Two factor authenticator activated successfully']);
        } else {
            return responseError('invalid_code', ['Wrong verification code']);
        }
    }

    public function disable2fa(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = Status::DISABLE;
            $user->save();
            return responseSuccess('2fa_disabled', ['Two factor authenticator deactivated successfully']);
        } else {
            return responseError('invalid_code', ['Wrong verification code']);
        }
    }
}
