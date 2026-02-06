<?php

namespace App\Http\Controllers\Advertiser;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\Campaign;
use App\Models\Conversion;
use App\Models\Deposit;
use App\Models\DeviceToken;
use App\Models\Form;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdvertiserController extends Controller {
    public function home() {
        $pageTitle  = 'Dashboard';
        $advertiser = auth()->guard('advertiser')->user();

        $conversionQuery = Conversion::whereHas('campaign', function ($q) use ($advertiser) {
            $q->where('advertiser_id', $advertiser->id);
        });

        $totalSpent = (clone $conversionQuery)
            ->whereHas('campaign', function ($q) {
                $q->where('status', Status::CAMPAIGN_COMPLETED);
            })
            ->sum(\DB::raw('user_payout + admin_commission'));

        $totalConversions = (clone $conversionQuery)->count();

        $monthlyConversions = (clone $conversionQuery)
            ->selectRaw("MONTH(created_at) as month, COUNT(*) as total")
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyConversions[$i] ?? 0;
        }

        $campaignQuery = Campaign::where('advertiser_id', $advertiser->id);

        $runningCampaigns = (clone $campaignQuery)
            ->where('status', Status::CAMPAIGN_APPROVED)
            ->where('is_paused', 0)
            ->count();

        $completedCampaigns = (clone $campaignQuery)
            ->where('status', Status::CAMPAIGN_COMPLETED)
            ->count();

        $depositQuery    = Deposit::where('advertiser_id', $advertiser->id)->successful();
        $totalDeposit    = (clone $depositQuery)->sum('amount');
        $withdrawalQuery = Withdrawal::where('advertiser_id', $advertiser->id)->approved();

        $totalWithdrawal = (clone $withdrawalQuery)->sum('amount');
        $transactions    = Transaction::where('advertiser_id', $advertiser->id)->orderBy('id', 'desc')->get();
        $answerTicket    = SupportTicket::where('advertiser_id', advertiserId())->where('status', Status::TICKET_ANSWER)->count();

        $stats = [
            'totalConversions' => $totalConversions,
            'totalSpend'       => $totalSpent,
            'totalDeposit'     => $totalDeposit,
            'totalWithdrawal'  => $totalWithdrawal,
            'totalTransaction' => $transactions->count(),
            'answerTicket'     => $answerTicket,
        ];

        $topCampaigns = (clone $campaignQuery)->running()
            ->withCount('conversions')
            ->orderByDesc('conversions_count')
            ->take(5)
            ->get();

        $campaigns = Campaign::where(function ($query) {
            $query->where('status', Status::CAMPAIGN_APPROVED)->orWhere('status', Status::CAMPAIGN_COMPLETED);
        })->where('advertiser_id', $advertiser->id)->get();
        $approvedCampaigns = $campaigns->filter(function ($campaign) {
            return $campaign->status == Status::CAMPAIGN_APPROVED;
        })->take(5);
        return view('Template::advertiser.dashboard', compact('pageTitle', 'totalSpent', 'runningCampaigns', 'completedCampaigns', 'chartData', 'stats', 'topCampaigns', 'transactions', 'campaigns', 'approvedCampaigns'));
    }

    public function payoutReport(Request $request) {
        $campaign    = Campaign::where('advertiser_id', advertiserId())->where('id', $request->campaign_id)->first();
        $conversions = Conversion::where('campaign_id', $campaign->id)->where('created_at', '>=', now()->subDays(30))->orderBy('created_at', 'asc')->get();

        $grouped = $conversions->groupBy(function ($item) {
            return showDateTime($item->created_at, 'Y-m-d');
        });

        $campaignId = $campaign->id;
        $data       = $grouped->map(function ($items, $date) use ($campaignId) {
            return [
                'x' => $date,
                'y' => $items->sum('user_payout'),
            ];
        })->sortKeys();

        return response()->json([
            'chartData' => [[
                'name' => "$campaign->title",
                'data' => $data->values(),
            ]],
        ]);
    }

    public function depositHistory(Request $request) {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->guard('advertiser')->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::advertiser.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function show2faForm() {
        $ga         = new GoogleAuthenticator();
        $advertiser = auth()->guard('advertiser')->user();
        $secret     = $ga->createSecret();
        $qrCodeUrl  = $ga->getQRCodeGoogleUrl($advertiser->username . '@' . gs('site_name'), $secret);
        $pageTitle  = '2FA Security';
        return view('Template::advertiser.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request) {
        $advertiser = auth()->guard('advertiser')->user();
        $request->validate([
            'key'  => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($advertiser, $request->code, $request->key);
        if ($response) {
            $advertiser->tsc = $request->key;
            $advertiser->ts  = Status::ENABLE;
            $advertiser->save();
            $notify[] = ['success', 'Two factor authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request) {
        $request->validate([
            'code' => 'required',
        ]);

        $advertiser = auth()->guard('advertiser')->user();
        $response   = verifyG2fa($advertiser, $request->code);
        if ($response) {
            $advertiser->tsc = null;
            $advertiser->ts  = Status::DISABLE;
            $advertiser->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function transactions() {
        $pageTitle    = 'Transactions';
        $remarks      = Transaction::distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::where('advertiser_id', advertiserId())->searchable(['trx'])->filter(['trx_type', 'remark'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::advertiser.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function kycForm() {
        if (auth()->guard('advertiser')->user()->kv == Status::KYC_PENDING) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('advertiser.home')->withNotify($notify);
        }
        if (auth()->guard('advertiser')->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('advertiser.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'advertiser_kyc')->first();
        return view('Template::advertiser.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycData() {
        $advertiser = auth()->guard('advertiser')->user();
        $pageTitle  = 'KYC Data';
        abort_if($advertiser->kv == Status::VERIFIED, 403);
        return view('Template::advertiser.kyc.info', compact('pageTitle', 'advertiser'));
    }

    public function kycSubmit(Request $request) {
        $form           = Form::where('act', 'advertiser_kyc')->firstOrFail();
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $advertiser = auth()->guard('advertiser')->user();
        foreach (isset($advertiser->kyc_data) ? $advertiser->kyc_data : [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }
        $advertiserData                   = $formProcessor->processFormData($request, $formData);
        $advertiser->kyc_data             = $advertiserData;
        $advertiser->kyc_rejection_reason = null;
        $advertiser->kv                   = Status::KYC_PENDING;
        $advertiser->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('advertiser.home')->withNotify($notify);
    }

    public function advertiserData() {
        $advertiser = auth()->guard('advertiser')->user();

        if ($advertiser->profile_complete == Status::YES) {
            return to_route('advertiser.home');
        }

        $pageTitle  = 'Advertiser Data';
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = isset($info['code']) ? implode(',', $info['code']) : '';
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        return view('Template::advertiser.advertiser_data', compact('pageTitle', 'advertiser', 'countries', 'mobileCode'));
    }

    public function advertiserDataSubmit(Request $request) {

        $advertiser = auth()->guard('advertiser')->user();

        if ($advertiser->profile_complete == Status::YES) {
            return to_route('advertiser.home');
        }

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:advertisers|min:6',
            'mobile'       => ['required', 'regex:/^([0-9]*)$/', Rule::unique('advertisers')->where('dial_code', $request->mobile_code)],
        ]);

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $advertiser->country_code = $request->country_code;
        $advertiser->mobile       = $request->mobile;
        $advertiser->username     = $request->username;

        $advertiser->address      = $request->address;
        $advertiser->city         = $request->city;
        $advertiser->state        = $request->state;
        $advertiser->zip          = $request->zip;
        $advertiser->country_name = isset($request->country) ? $request->country : '';
        $advertiser->dial_code    = $request->mobile_code;

        $advertiser->profile_complete = Status::YES;
        $advertiser->save();

        return to_route('advertiser.home');
    }

    public function addDeviceToken(Request $request) {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()->all()];
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            return ['success' => true, 'message' => 'Already exists'];
        }

        $deviceToken                = new DeviceToken();
        $deviceToken->advertiser_id = auth()->guard('advertiser')->user()->id;
        $deviceToken->token         = $request->token;
        $deviceToken->is_app        = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
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

    public function trackingSnippets() {
        $pageTitle = 'Tracking Snippets';
        return view('Template::advertiser.tracking_snippets', compact('pageTitle'));
    }
}
