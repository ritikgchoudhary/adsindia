<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use App\Lib\WatchPayGateway;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    private const MAIN_WITHDRAW_GST_PERCENT = 18.0;

    public function withdrawMethod()
    {
        $user = auth()->user();

        // Withdraw only after KYC verified – else frontend will redirect to KYC page
        if ((int) ($user->kv ?? 0) !== Status::KYC_VERIFIED) {
            return responseError('kyc_required', [
                'KYC verification is required to withdraw. Please complete KYC first.',
            ], null);
        }

        // Withdraw options come from user's KYC (bank / UPI)
        $bankName = $user->bank_name ?? 'Bank Account';
        $accountNumber = (string) ($user->account_number ?? '');
        $upiId = (string) ($user->upi_id ?? '');

        $maskedAccount = $accountNumber ? ('****' . substr($accountNumber, -4)) : '';
        $bankLabel = trim((string) $bankName) . ($maskedAccount ? (' - ' . $maskedAccount) : '');
        $upiLabel = $upiId ? ('UPI - ' . $upiId) : '';

        $defaultMethod = WithdrawMethod::active()->first();
        $minLimit = $defaultMethod ? (float) ($defaultMethod->min_limit ?? 0) : 0;
        $maxLimit = $defaultMethod ? (float) ($defaultMethod->max_limit ?? 100000) : 100000;
        $percentCharge = $defaultMethod ? (float) ($defaultMethod->percent_charge ?? 0) : 0;
        $fixedCharge = $defaultMethod ? (float) ($defaultMethod->fixed_charge ?? 0) : 0;
        $currency = $defaultMethod ? ($defaultMethod->currency ?? 'INR') : 'INR';
        $rate = $defaultMethod ? (float) ($defaultMethod->rate ?? 1) : 1;
        $methodId = $defaultMethod ? $defaultMethod->id : 0;

        $imagePath = null;
        if ($defaultMethod && $defaultMethod->image) {
            $imageFile = getFilePath('withdrawMethod') . '/' . $defaultMethod->image;
            if (file_exists($imageFile)) {
                $imagePath = getImage($imageFile, getFileSize('withdrawMethod'));
            }
        }
        if (!$imagePath) {
            // Safer fallback (avoid placeholder route/size issues)
            $imagePath = asset('assets/images/default.png');
        }

        $methods = [];

        if ($bankLabel !== '') {
            $methods[] = [
                'id' => $methodId,
                'name' => $bankLabel,
                'image' => $imagePath,
                'min_limit' => $minLimit,
                'max_limit' => $maxLimit,
                'percent_charge' => $percentCharge,
                'fixed_charge' => $fixedCharge,
                'currency' => $currency,
                'rate' => $rate,
                'from_kyc' => true,
                'payout_type' => 'bank',
            ];
        }

        if ($upiLabel !== '') {
            $methods[] = [
                'id' => $methodId,
                'name' => $upiLabel,
                'image' => $imagePath,
                'min_limit' => $minLimit,
                'max_limit' => $maxLimit,
                'percent_charge' => $percentCharge,
                'fixed_charge' => $fixedCharge,
                'currency' => $currency,
                'rate' => $rate,
                'from_kyc' => true,
                'payout_type' => 'upi',
            ];
        }

        if (count($methods) === 0) {
            $methods[] = [
                'id' => $methodId,
                'name' => 'Bank Account',
                'image' => $imagePath,
                'min_limit' => $minLimit,
                'max_limit' => $maxLimit,
                'percent_charge' => $percentCharge,
                'fixed_charge' => $fixedCharge,
                'currency' => $currency,
                'rate' => $rate,
                'from_kyc' => true,
                'payout_type' => 'bank',
            ];
        }

        return response()->json([
            'remark' => 'withdraw_methods',
            'status' => 'success',
            'message' => ['Withdrawal methods retrieved successfully'],
            'data' => $methods,
        ]);
    }

    public function withdrawStore(Request $request)
    {
        $request->validate([
            'method_code' => 'required',
            // Amount is no longer user-editable in UI; we withdraw full balance.
            // Keep this optional for backward compatibility.
            'amount' => 'nullable|numeric|min:0.01',
            'payout_type' => 'nullable|in:bank,upi',
        ]);

        $method = WithdrawMethod::where('id', $request->method_code)->active()->firstOrFail();
        $user = auth()->user();

        // STEP 3: Check if KYC is approved before allowing withdrawal (kv = 1 = KYC_VERIFIED)
        $kycStatus = (int) ($user->kv ?? 0);
        if ($kycStatus !== Status::KYC_VERIFIED) {
            return response()->json([
                'status' => 'error',
                'remark' => 'kyc_verification',
                'message' => ['error' => [
                    'KYC verification is required before withdrawal. Please complete and get your KYC approved first.'
                ]]
            ], 400);
        }

        // Payout type validation (from KYC)
        $payoutType = (string) ($request->input('payout_type') ?: 'bank');
        if ($payoutType === 'upi') {
            if (!trim((string) ($user->upi_id ?? ''))) {
                return response()->json([
                    'status' => 'error',
                    'message' => ['UPI ID not found in your account. Please add UPI ID in Account & KYC.']
                ], 400);
            }
        } else {
            if (!trim((string) ($user->account_number ?? ''))) {
                return response()->json([
                    'status' => 'error',
                    'message' => ['Bank account details not found. Please add bank details in Account & KYC.']
                ], 400);
            }
        }

        // Main wallet withdrawal: FULL balance withdrawal only (amount is not editable).
        // 18% fee applies on the full balance, and is deducted from the same wallet balance.
        $userBalance = (float) ($user->balance ?? 0);
        if ($userBalance <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['No balance available to withdraw']
            ], 400);
        }

        $requestAmount = (float) $userBalance; // withdraw full wallet balance

        $gstPercent = 18;
        $gstFee = (float) $requestAmount * ((float) $gstPercent / 100);
        $methodCharge = (float) $method->fixed_charge + ((float) $requestAmount * (float) $method->percent_charge / 100);
        $totalCharge = (float) $gstFee + (float) $methodCharge;

        // Net payout = balance - charges
        $afterCharge = (float) $requestAmount - (float) $totalCharge;
        if ($afterCharge <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient balance for charges. Please contact support.']
            ], 400);
        }

        // Total deduction from wallet is the full balance (withdraw all)
        $totalDeduction = (float) $requestAmount;
        $finalAmount = (float) $afterCharge * (float) ($method->rate ?? 1);

        // Direct withdrawal request (pending review). Deduct from wallet immediately.
        $withdraw = new Withdrawal();
        $withdraw->method_id = $method->id;
        $withdraw->user_id = $user->id;
        $withdraw->amount = $requestAmount;
        $withdraw->currency = $method->currency;
        $withdraw->rate = $method->rate;
        $withdraw->charge = $totalCharge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx = getTrx();
        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->wallet = 'main';
        $withdraw->withdraw_information = [
            ['name' => 'payout_type', 'type' => 'text', 'value' => $payoutType],
            ['name' => 'withdraw_fee_percent', 'type' => 'text', 'value' => (string) $gstPercent],
            ['name' => 'withdraw_fee_amount', 'type' => 'text', 'value' => (string) $gstFee],
        ];
        $withdraw->save();

        // Deduct full balance from main wallet (withdraw all)
        $user->balance = (float) $user->balance - (float) $requestAmount;
        $user->save();

        // Transaction record
        $withdrawTransaction = new Transaction();
        $withdrawTransaction->user_id = $user->id;
        $withdrawTransaction->amount = $withdraw->amount;
        $withdrawTransaction->post_balance = $user->balance;
        $withdrawTransaction->charge = $withdraw->charge;
        $withdrawTransaction->trx_type = '-';
        $withdrawTransaction->details = 'Withdraw request (' . strtoupper($payoutType) . ') - gross ' . showAmount($withdraw->amount) . ', fee+charges ' . showAmount($withdraw->charge) . ', net ' . showAmount($withdraw->after_charge) . ' via ' . ($withdraw->method->name ?? 'Withdraw Method');
        $withdrawTransaction->trx = $withdraw->trx;
        $withdrawTransaction->remark = 'withdraw';
        $withdrawTransaction->wallet = 'main';
        $withdrawTransaction->save();

        // Admin notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New withdraw request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Withdrawal request created. Full balance will be withdrawn; 18% fee + method charges will be deducted from it.'],
            'data' => [
                'withdraw_id' => $withdraw->id,
                'trx' => $withdraw->trx,
                'amount' => $withdraw->amount,
                'total_charge' => $totalCharge,
                'total_deduction' => $totalDeduction,
                'final_amount' => $finalAmount,
                'after_charge' => $afterCharge,
            ]
        ]);
    }

    /**
     * NEW: Pay GST (18%) via gateway first, then auto-create withdrawal.
     * Flow:
     * 1) POST withdraw-request/gst/initiate -> returns payment_url (WatchPay)
     * 2) After gateway success, SPA calls withdraw-request/gst/confirm with trx
     * 3) Backend creates Withdrawal (status pending) and deducts wallet balance
     */
    public function initiateWithdrawGstPayment(Request $request)
    {
        $request->validate([
            'method_code' => 'required',
            'payout_type' => 'nullable|in:bank,upi',
            'gateway' => 'nullable|string|in:watchpay,simplypay',
        ]);

        $user = auth()->user();

        // KYC must be verified (middleware 'kyc' already, but keep explicit)
        if ((int) ($user->kv ?? 0) !== Status::KYC_VERIFIED) {
            return response()->json([
                'status' => 'error',
                'remark' => 'kyc_verification',
                'message' => ['error' => [
                    'KYC verification is required before withdrawal. Please complete and get your KYC approved first.'
                ]]
            ], 400);
        }

        $method = WithdrawMethod::where('id', $request->method_code)->active()->firstOrFail();

        // Payout type validation (from KYC)
        $payoutType = (string) ($request->input('payout_type') ?: 'bank');
        if ($payoutType === 'upi') {
            if (!trim((string) ($user->upi_id ?? ''))) {
                return response()->json([
                    'status' => 'error',
                    'message' => ['UPI ID not found in your account. Please add UPI ID in Account & KYC.']
                ], 400);
            }
        } else {
            if (!trim((string) ($user->account_number ?? ''))) {
                return response()->json([
                    'status' => 'error',
                    'message' => ['Bank account details not found. Please add bank details in Account & KYC.']
                ], 400);
            }
        }

        $general = gs();
        $balance = (float) ($user->balance ?? 0);
        if ($balance <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['No balance available to withdraw']
            ], 400);
        }

        $withdrawAmount = (float) $balance; // Full balance withdrawal (same as current UI)
        $gstAmount = round($withdrawAmount * (self::MAIN_WITHDRAW_GST_PERCENT / 100), 2);
        if ($gstAmount <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['GST amount is invalid. Please contact support.']
            ], 400);
        }

        $trx = getTrx();
        $gateway = $request->input('gateway', 'watchpay');

        $cacheKey = ($gateway === 'simplypay' ? 'simplypay_payment_' : 'watchpay_payment_') . $trx;
        cache()->put($cacheKey, [
            'type' => 'withdraw_gst',
            'user_id' => (int) $user->id,
            'withdraw_amount' => (float) $withdrawAmount,
            'gst_percent' => (float) self::MAIN_WITHDRAW_GST_PERCENT,
            'gst_amount' => (float) $gstAmount,
            'method_id' => (int) $method->id,
            'payout_type' => $payoutType,
            'status' => 'pending',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ], now()->addHours(2));

        $base = $request->getSchemeAndHttpHost() ?: rtrim((string) config('app.url'), '/');
        $pageUrl = $base . '/user/withdraw?' . ($gateway === 'simplypay' ? 'simplypay_trx=' : 'watchpay_trx=') . urlencode($trx) . '&withdraw_gst=1';
        $notifyUrl = $base . '/ipn/' . $gateway;

        try {
            if ($gateway === 'simplypay') {
                $sp = \App\Lib\SimplyPayGateway::createPayment([
                    'merOrderNo' => $trx,
                    'amount' => $gstAmount,
                    'notifyUrl' => $notifyUrl,
                    'returnUrl' => $pageUrl,
                    'name' => $user->fullname ?: $user->username,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'attach' => 'GST Payment for Withdrawal',
                ]);
                $paymentUrl = $sp['pay_link'];
            } else {
                $wp = WatchPayGateway::createWebPayment(
                    $trx,
                    (float) $gstAmount,
                    'GST Payment for Withdrawal',
                    $pageUrl,
                    $notifyUrl
                );
                $paymentUrl = $wp['pay_link'];
            }
        } catch (\Throwable $e) {
            return responseError('payment_gateway_error', ['Payment gateway init failed. Please try again.']);
        }

        return responseSuccess('withdraw_gst_payment_initiated', ['GST payment initialized. Complete payment to submit withdrawal.'], [
            'payment_url' => $paymentUrl,
            'trx' => $trx,
            'withdraw_amount' => (float) $withdrawAmount,
            'gst_percent' => (float) self::MAIN_WITHDRAW_GST_PERCENT,
            'gst_amount' => (float) $gstAmount,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'gateway_name' => ($gateway === 'simplypay' ? 'SimplyPay' : 'WatchPay'),
        ]);
    }

    public function confirmWithdrawGstPayment(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
            'gateway' => 'nullable|string|in:watchpay,simplypay',
        ]);

        $user = auth()->user();
        $trx = (string) $request->trx;
        $gateway = $request->input('gateway', 'watchpay');

        $cacheKey = ($gateway === 'simplypay' ? 'simplypay_payment_' : 'watchpay_payment_') . $trx;
        $session = cache()->get($cacheKey);
        if (!is_array($session) || ($session['type'] ?? '') !== 'withdraw_gst' || (int)($session['user_id'] ?? 0) !== (int)$user->id) {
            return responseError('payment_not_found', ['Payment session not found. Please initiate GST payment again.']);
        }
        if (($session['status'] ?? '') !== 'success') {
            return responseError('payment_pending', ['GST payment not verified yet. Please complete payment and try again.']);
        }

        // Idempotent: if withdrawal already created for this trx, return success
        $existing = Withdrawal::where('trx', $trx)->where('user_id', $user->id)->first();
        if ($existing) {
            return responseSuccess('withdrawal_already_created', ['Withdrawal already created for this payment.'], [
                'withdraw_id' => $existing->id,
                'trx' => $existing->trx,
            ]);
        }

        // Re-check KYC (safety)
        if ((int) ($user->kv ?? 0) !== Status::KYC_VERIFIED) {
            return responseError('kyc_required', ['KYC verification is required before withdrawal.']);
        }

        $withdrawAmount = (float) ($session['withdraw_amount'] ?? 0);
        $gstPercent = (float) ($session['gst_percent'] ?? self::MAIN_WITHDRAW_GST_PERCENT);
        $gstAmount = (float) ($session['gst_amount'] ?? 0);
        $methodId = (int) ($session['method_id'] ?? 0);
        $payoutType = (string) ($session['payout_type'] ?? 'bank');

        if ($withdrawAmount <= 0 || $gstAmount <= 0 || $methodId <= 0) {
            return responseError('invalid_session', ['Invalid payment session data. Please initiate payment again.']);
        }

        // User must still have the same/full balance available
        if (((float) ($user->balance ?? 0)) < $withdrawAmount) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient balance to withdraw. Please refresh and try again.']
            ], 400);
        }

        $method = WithdrawMethod::where('id', $methodId)->active()->first();
        if (!$method) {
            return responseError('withdraw_method_not_found', ['Withdrawal method not found. Please select a method and try again.']);
        }

        // Method charges apply in withdrawal; GST is paid separately via gateway
        $methodCharge = (float) $method->fixed_charge + ((float) $withdrawAmount * (float) $method->percent_charge / 100);
        $afterCharge = (float) $withdrawAmount - (float) $methodCharge;
        if ($afterCharge <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['Withdraw amount must be sufficient for charges']
            ], 400);
        }
        $finalAmount = (float) $afterCharge * (float) ($method->rate ?? 1);

        // Create withdrawal (pending review) and deduct wallet now
        $withdraw = new Withdrawal();
        $withdraw->method_id = $method->id;
        $withdraw->user_id = $user->id;
        $withdraw->amount = $withdrawAmount;
        $withdraw->currency = $method->currency;
        $withdraw->rate = $method->rate;
        $withdraw->charge = $methodCharge; // GST NOT included here (paid externally)
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx = $trx; // link withdrawal to payment trx
        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->wallet = 'main';
        $withdraw->withdraw_information = [
            ['name' => 'payout_type', 'type' => 'text', 'value' => $payoutType],
            ['name' => 'gst_percent', 'type' => 'text', 'value' => (string) $gstPercent],
            ['name' => 'gst_amount', 'type' => 'text', 'value' => (string) $gstAmount],
            ['name' => 'gst_paid_trx', 'type' => 'text', 'value' => $trx],
            ['name' => 'gst_paid_at', 'type' => 'text', 'value' => now()->format('Y-m-d H:i:s')],
            ['name' => 'agent_id', 'type' => 'text', 'value' => (string) ((int) ($user->ref_by ?? 0))],
        ];
        $withdraw->save();

        // Deduct full withdrawal amount from wallet
        $user->balance = (float) $user->balance - (float) $withdrawAmount;
        $user->save();

        // Transaction record (main wallet)
        $withdrawTransaction = new Transaction();
        $withdrawTransaction->user_id = $user->id;
        $withdrawTransaction->amount = $withdraw->amount;
        $withdrawTransaction->post_balance = $user->balance;
        $withdrawTransaction->charge = $withdraw->charge;
        $withdrawTransaction->trx_type = '-';
        $withdrawTransaction->details = 'Withdraw request (' . strtoupper($payoutType) . ') - GST paid separately via gateway, trx ' . $trx;
        $withdrawTransaction->trx = $withdraw->trx;
        $withdrawTransaction->remark = 'withdraw';
        $withdrawTransaction->wallet = 'main';
        $withdrawTransaction->save();

        // Admin notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New withdraw request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        return responseSuccess('withdrawal_created', ['GST paid successfully. Withdrawal request submitted for review.'], [
            'withdraw_id' => $withdraw->id,
            'trx' => $withdraw->trx,
            'withdraw_amount' => (float) $withdrawAmount,
            'gst_amount' => (float) $gstAmount,
            'method_charge' => (float) $methodCharge,
            'after_charge' => (float) $afterCharge,
        ]);
    }

    public function payWithdrawalFee(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
        ]);

        $user = auth()->user();
        $withdraw = Withdrawal::where('trx', $request->trx)
            ->where('user_id', $user->id)
            ->where('status', Status::PAYMENT_INITIATE)
            ->firstOrFail();

        // Backward compatibility for older withdrawals created as PAYMENT_INITIATE.
        // /user/withdraw no longer uses any separate 18% fee.

        if ($user->balance < $withdraw->amount) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient balance. You need ' . showAmount($withdraw->amount) . ' (full balance) to withdraw.']
            ], 400);
        }

        // Deduct full withdrawal amount from balance
        $user->balance -= $withdraw->amount;
        $user->save();

        // Create transaction record for withdrawal
        $withdrawTransaction = new Transaction();
        $withdrawTransaction->user_id = $user->id;
        $withdrawTransaction->amount = $withdraw->amount;
        $withdrawTransaction->post_balance = $user->balance;
        $withdrawTransaction->charge = $withdraw->charge;
        $withdrawTransaction->trx_type = '-';
        $withdrawTransaction->details = 'Withdraw request via ' . $withdraw->method->name;
        $withdrawTransaction->trx = $withdraw->trx;
        $withdrawTransaction->remark = 'withdraw';
        $withdrawTransaction->wallet = 'main';
        $withdrawTransaction->save();

        // Update withdrawal status to PENDING (fee paid, ready for admin review)
        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->save();

        // Create admin notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New withdraw request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Withdrawal request has been submitted for review.'],
            'data' => [
                'trx' => $withdraw->trx,
                'withdrawal_amount' => $withdraw->amount,
            ]
        ]);
    }

    public function withdrawLog()
    {
        $user = auth()->user();
        $general = gs();

        $search = request()->get('search');
        $q = Withdrawal::where('user_id', $user->id)->with('method')->orderBy('id', 'desc');
        if ($search) {
            $q->where('trx', $search);
        }

        $withdrawals = $q->get()->map(function ($withdraw) {
            return [
                'id' => $withdraw->id,
                'trx' => $withdraw->trx,
                'method' => [
                    'name' => $withdraw->method->name ?? 'N/A',
                ],
                'amount' => (float) $withdraw->amount,
                'charge' => (float) ($withdraw->charge ?? 0),
                'after_charge' => (float) ($withdraw->after_charge ?? ((float) $withdraw->amount - (float) ($withdraw->charge ?? 0))),
                'final_amount' => (float) ($withdraw->final_amount ?? 0),
                'currency' => $withdraw->currency ?? 'INR',
                'rate' => (float) ($withdraw->rate ?? 1),
                'status' => $withdraw->status,
                'status_text' => $this->getStatusText($withdraw->status),
                'status_badge' => $withdraw->status_badge ?? '',
                'withdraw_information' => $withdraw->withdraw_information ?? [],
                'admin_feedback' => $withdraw->admin_feedback ?? '',
                'created_at' => $withdraw->created_at ? $withdraw->created_at->format('Y-m-d H:i:s') : null,
                'created_at_human' => $withdraw->created_at ? $withdraw->created_at->diffForHumans() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $withdrawals,
                'currency_symbol' => $general->cur_sym ?? '₹',
            ],
        ]);
    }

    private function getStatusText($status)
    {
        $statuses = [
            Status::PAYMENT_INITIATE => 'Fee Pending',
            Status::PAYMENT_PENDING => 'Pending Review',
            Status::PAYMENT_SUCCESS => 'Approved',
            Status::PAYMENT_REJECT => 'Rejected',
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    /**
     * Affiliate wallet: same withdraw method list (KYC gated).
     */
    public function affiliateWithdrawMethod()
    {
        return $this->withdrawMethod();
    }

    /**
     * Affiliate wallet withdrawal request (full affiliate balance only).
     */
    public function affiliateWithdrawStore(Request $request)
    {
        $request->validate([
            'method_code' => 'required',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $method = WithdrawMethod::where('id', $request->method_code)->active()->firstOrFail();
        $user = auth()->user();

        $kycStatus = (int) ($user->kv ?? 0);
        if ($kycStatus !== Status::KYC_VERIFIED) {
            return response()->json([
                'status' => 'error',
                'remark' => 'kyc_verification',
                'message' => ['error' => [
                    'KYC verification is required before withdrawal. Please complete and get your KYC approved first.'
                ]]
            ], 400);
        }

        // Full affiliate balance only (within 1 unit tolerance)
        $walletBalance = (float) ($user->affiliate_balance ?? 0);
        $requestAmount = (float) $request->amount;
        if ($walletBalance <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['No affiliate balance available to withdraw']
            ], 400);
        }
        if (abs($requestAmount - $walletBalance) > 1) {
            return response()->json([
                'status' => 'error',
                'message' => ['You can only withdraw your full affiliate balance at once. Amount must match your current affiliate balance.']
            ], 400);
        }
        $request->merge(['amount' => $walletBalance]);

        // Affiliate wallet: NO 18% fee/GST. Only method charges apply (if any).
        $methodCharge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
        $totalCharge = $methodCharge;
        $afterCharge = $request->amount - $totalCharge;

        if ($afterCharge <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => ['Withdraw amount must be sufficient for charges']
            ], 400);
        }

        $finalAmount = $afterCharge * $method->rate;

        // Direct withdraw: deduct immediately from affiliate wallet and mark as pending review
        $trx = getTrx();

        $user->affiliate_balance = (float) ($user->affiliate_balance ?? 0) - (float) $request->amount;
        if ($user->affiliate_balance < -0.00000001) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient affiliate balance']
            ], 400);
        }
        $user->save();

        $withdraw = new Withdrawal();
        $withdraw->method_id = $method->id;
        $withdraw->user_id = $user->id;
        $withdraw->amount = $request->amount;
        $withdraw->currency = $method->currency;
        $withdraw->rate = $method->rate;
        $withdraw->charge = $totalCharge;
        $withdraw->final_amount = $finalAmount;
        $withdraw->after_charge = $afterCharge;
        $withdraw->trx = $trx;
        $withdraw->status = Status::PAYMENT_PENDING; // direct pending review
        $withdraw->wallet = 'affiliate';
        $withdraw->save();

        $withdrawTransaction = new Transaction();
        $withdrawTransaction->user_id = $user->id;
        $withdrawTransaction->amount = $withdraw->amount;
        $withdrawTransaction->post_balance = (float) ($user->affiliate_balance ?? 0);
        $withdrawTransaction->charge = $withdraw->charge;
        $withdrawTransaction->trx_type = '-';
        $withdrawTransaction->details = 'Affiliate wallet withdraw request via ' . ($method->name ?? 'Withdraw Method');
        $withdrawTransaction->trx = $trx;
        $withdrawTransaction->remark = 'affiliate_withdraw';
        $withdrawTransaction->wallet = 'affiliate';
        $withdrawTransaction->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New affiliate wallet withdraw request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Affiliate withdrawal request created and submitted for review.'],
            'data' => [
                'withdraw_id' => $withdraw->id,
                'trx' => $withdraw->trx,
                'amount' => $request->amount,
                'total_charge' => $totalCharge,
                'final_amount' => $finalAmount,
            ]
        ]);
    }

    /**
     * Affiliate wallet fee payment & submit request for review.
     */
    public function affiliatePayWithdrawalFee(Request $request)
    {
        $request->validate([
            'trx' => 'required|string',
        ]);

        $user = auth()->user();
        $withdraw = Withdrawal::where('trx', $request->trx)
            ->where('user_id', $user->id)
            ->where('wallet', 'affiliate')
            ->where('status', Status::PAYMENT_INITIATE)
            ->firstOrFail();

        // Backward compatibility for older affiliate withdrawals that were created with "fee pending".
        // Affiliate wallet has NO 18% fee/GST.
        if (((float) ($user->affiliate_balance ?? 0)) < (float) $withdraw->amount) {
            return response()->json([
                'status' => 'error',
                'message' => ['Insufficient affiliate balance.']
            ], 400);
        }

        $user->affiliate_balance = (float) ($user->affiliate_balance ?? 0) - (float) $withdraw->amount;
        $user->save();

        // Transaction record (affiliate wallet)
        $withdrawTransaction = new Transaction();
        $withdrawTransaction->user_id = $user->id;
        $withdrawTransaction->amount = $withdraw->amount;
        $withdrawTransaction->post_balance = (float) ($user->affiliate_balance ?? 0);
        $withdrawTransaction->charge = $withdraw->charge;
        $withdrawTransaction->trx_type = '-';
        $withdrawTransaction->details = 'Affiliate wallet withdraw request via ' . ($withdraw->method->name ?? 'Withdraw Method');
        $withdrawTransaction->trx = $withdraw->trx;
        $withdrawTransaction->remark = 'affiliate_withdraw';
        $withdrawTransaction->wallet = 'affiliate';
        $withdrawTransaction->save();

        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New affiliate wallet withdraw request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.data.details', $withdraw->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'message' => ['Withdrawal submitted for review.'],
            'data' => [
                'trx' => $withdraw->trx,
                'withdrawal_amount' => $withdraw->amount,
            ]
        ]);
    }

    /**
     * Affiliate wallet withdrawal history.
     */
    public function affiliateWithdrawLog()
    {
        $user = auth()->user();
        $general = gs();

        $search = request()->get('search');
        $q = Withdrawal::where('user_id', $user->id)
            ->where('wallet', 'affiliate')
            ->with('method')
            ->orderBy('id', 'desc');

        if ($search) {
            $q->where('trx', $search);
        }

        $withdrawals = $q->get()->map(function ($withdraw) {
            return [
                'id' => $withdraw->id,
                'trx' => $withdraw->trx,
                'method' => [
                    'name' => $withdraw->method->name ?? 'N/A',
                ],
                'amount' => (float) $withdraw->amount,
                'charge' => (float) ($withdraw->charge ?? 0),
                'after_charge' => (float) ($withdraw->after_charge ?? ((float) $withdraw->amount - (float) ($withdraw->charge ?? 0))),
                'final_amount' => (float) ($withdraw->final_amount ?? 0),
                'currency' => $withdraw->currency ?? 'INR',
                'rate' => (float) ($withdraw->rate ?? 1),
                'status' => $withdraw->status,
                'status_text' => $this->getStatusText($withdraw->status),
                'status_badge' => $withdraw->status_badge ?? '',
                'withdraw_information' => $withdraw->withdraw_information ?? [],
                'admin_feedback' => $withdraw->admin_feedback ?? '',
                'created_at' => $withdraw->created_at ? $withdraw->created_at->format('Y-m-d H:i:s') : null,
                'created_at_human' => $withdraw->created_at ? $withdraw->created_at->diffForHumans() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $withdrawals,
                'currency_symbol' => $general->cur_sym ?? '₹',
            ],
        ]);
    }
}
