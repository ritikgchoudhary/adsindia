<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\Status;
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

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
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

        $earnings = [
            'today' => Transaction::where('user_id', $user->id)
                ->where('trx_type', '+')
                ->where('created_at', '>=', $today)
                ->sum('amount'),
            'last7Days' => Transaction::where('user_id', $user->id)
                ->where('trx_type', '+')
                ->where('created_at', '>=', $last7Days)
                ->sum('amount'),
            'last30Days' => Transaction::where('user_id', $user->id)
                ->where('trx_type', '+')
                ->where('created_at', '>=', $last30Days)
                ->sum('amount'),
            'total' => $widget['total_earning'] + $widget['ads_income'],
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

        return responseSuccess('account_kyc', ['Account KYC data retrieved successfully'], [
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
            ],
            'kyc_fee' => 990, // Fixed 990 fee for Account KYC
            'kyc_status' => $user->kv ?? 0,
            'kyc_rejection_reason' => $user->kyc_rejection_reason ?? '',
            'kyc_fee' => $general->kyc_fee ?? 0,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function updateBankDetails(Request $request)
    {
        $user = auth()->user();
        
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
            'status' => $user->status,
            'ev' => $user->ev,
            'sv' => $user->sv,
            'kv' => $user->kv,
            'kyc_rejection_reason' => $user->kyc_rejection_reason,
        ]);
    }

    public function submitProfile(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        $user = auth()->user();

        if ($request->hasFile('image')) {
            try {
                $old = $user->image;
                $user->image = fileUploader(
                    $request->image,
                    getFilePath('userProfile'),
                    getFileSize('userProfile'),
                    $old
                );
            } catch (\Exception $e) {
                return responseError('image_upload_failed', ['Could not upload your image']);
            }
        }

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = $request->address;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->zip = $request->zip;
        $user->save();

        return responseSuccess('profile_updated', ['Profile updated successfully']);
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
        $form = Form::where('act', 'kyc')->firstOrFail();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);

        $user = auth()->user();
        $general = gs();
        $kycFee = $general->kyc_fee ?? 0;

        // Check if KYC fee needs to be paid
        if ($kycFee > 0 && $user->balance < $kycFee) {
            return responseError('insufficient_balance', ['Insufficient balance to pay KYC fee']);
        }

        // Deduct KYC fee if applicable
        if ($kycFee > 0) {
            $user->balance -= $kycFee;
            
            // Create transaction for KYC fee
            $trx = getTrx();
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $kycFee;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '-';
            $transaction->details = 'KYC Application Fee';
            $transaction->trx = $trx;
            $transaction->remark = 'kyc_fee';
            $transaction->save();
        }

        // Remove old KYC files
        foreach (isset($user->kyc_data) ? $user->kyc_data : [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }

        $userData = $formProcessor->processFormData($request, $formData);
        $user->kyc_data = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv = Status::KYC_PENDING;
        $user->save();

        return responseSuccess('kyc_submitted', ['KYC data submitted successfully']);
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
