<?php
namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\AdvertiserNotificationSender;
use App\Models\Advertiser;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageAdvertisersController extends Controller {

    public function allAdvertisers() {
        $pageTitle   = 'All Advertisers';
        $advertisers = $this->advertiserData();
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function activeAdvertisers() {
        $pageTitle   = 'Active Advertisers';
        $advertisers = $this->advertiserData('active');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function bannedAdvertisers() {
        $pageTitle   = 'Banned Advertisers';
        $advertisers = $this->advertiserData('banned');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function emailUnverifiedAdvertisers() {
        $pageTitle   = 'Email Unverified Advertisers';
        $advertisers = $this->advertiserData('emailUnverified');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function kycUnverifiedAdvertisers() {
        $pageTitle   = 'KYC Unverified Advertisers';
        $advertisers = $this->advertiserData('kycUnverified');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function kycPendingAdvertisers() {
        $pageTitle   = 'KYC Pending Advertisers';
        $advertisers = $this->advertiserData('kycPending');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function emailVerifiedAdvertisers() {
        $pageTitle   = 'Email Verified Advertisers';
        $advertisers = $this->advertiserData('emailVerified');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function mobileUnverifiedAdvertisers() {
        $pageTitle   = 'Mobile Unverified Advertisers';
        $advertisers = $this->advertiserData('mobileUnverified');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function mobileVerifiedAdvertisers() {
        $pageTitle   = 'Mobile Verified Advertisers';
        $advertisers = $this->advertiserData('mobileVerified');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    public function advertisersWithBalance() {
        $pageTitle   = 'Advertisers with Balance';
        $advertisers = $this->advertiserData('withBalance');
        return view('admin.advertisers.list', compact('pageTitle', 'advertisers'));
    }

    protected function advertiserData($scope = null) {
        if ($scope) {
            $advertisers = Advertiser::$scope();
        } else {
            $advertisers = Advertiser::query();
        }
        return $advertisers->searchable(['username', 'email'])->orderBy('id', 'desc')->paginate(getPaginate());
    }

    public function detail($id) {
        $advertiser = Advertiser::findOrFail($id);
        $pageTitle  = 'Advertiser Detail - ' . $advertiser->username;

        $totalDeposit     = Deposit::where('advertiser_id', $advertiser->id)->successful()->sum('amount');
        $totalWithdrawals = Withdrawal::where('advertiser_id', $advertiser->id)->approved()->sum('amount');
        $totalTransaction = Transaction::where('advertiser_id', $advertiser->id)->count();
        $countries        = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.advertisers.detail', compact('pageTitle', 'advertiser', 'totalDeposit', 'totalWithdrawals', 'totalTransaction', 'countries'));
    }

    public function kycDetails($id) {
        $pageTitle  = 'KYC Details';
        $advertiser = Advertiser::findOrFail($id);
        return view('admin.advertisers.kyc_detail', compact('pageTitle', 'advertiser'));
    }

    public function kycApprove($id) {
        $advertiser     = Advertiser::findOrFail($id);
        $advertiser->kv = Status::KYC_VERIFIED;
        $advertiser->save();

        notify($advertiser, 'KYC_APPROVE', []);

        $notify[] = ['success', 'KYC approved successfully'];
        return to_route('admin.advertisers.kyc.pending')->withNotify($notify);
    }

    public function kycReject(Request $request, $id) {
        $request->validate([
            'reason' => 'required',
        ]);
        $advertiser                       = Advertiser::findOrFail($id);
        $advertiser->kv                   = Status::KYC_UNVERIFIED;
        $advertiser->kyc_rejection_reason = $request->reason;
        $advertiser->save();

        notify($advertiser, 'KYC_REJECT', [
            'reason' => $request->reason,
        ]);

        $notify[] = ['success', 'KYC rejected successfully'];
        return to_route('admin.advertisers.kyc.pending')->withNotify($notify);
    }

    public function update(Request $request, $id) {
        $advertiser   = Advertiser::findOrFail($id);
        $countryData  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray = (array) $countryData;
        $countries    = implode(',', array_keys($countryArray));

        $countryCode = $request->country;
        $country     = $countryData->$countryCode->country;
        $dialCode    = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => 'required|email|string|max:40|unique:advertisers,email,' . $advertiser->id,
            'mobile'    => 'required|string|max:40',
            'country'   => 'required|in:' . $countries,
        ]);

        $exists = Advertiser::where('mobile', $request->mobile)->where('dial_code', $dialCode)->where('id', '!=', $advertiser->id)->exists();
        if ($exists) {
            $notify[] = ['error', 'The mobile number already exists.'];
            return back()->withNotify($notify);
        }

        $advertiser->mobile    = $request->mobile;
        $advertiser->firstname = $request->firstname;
        $advertiser->lastname  = $request->lastname;
        $advertiser->email     = $request->email;

        $advertiser->address      = $request->address;
        $advertiser->city         = $request->city;
        $advertiser->state        = $request->state;
        $advertiser->zip          = $request->zip;
        $advertiser->country_name = $country;
        $advertiser->dial_code    = $dialCode;
        $advertiser->country_code = $countryCode;

        $advertiser->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $advertiser->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        $advertiser->ts = $request->ts ? Status::ENABLE : Status::DISABLE;
        if (!$request->kv) {
            $advertiser->kv = Status::KYC_UNVERIFIED;
            if ($advertiser->kyc_data) {
                foreach ($advertiser->kyc_data as $kycData) {
                    if ($kycData->type == 'file') {
                        fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
                    }
                }
            }
            $advertiser->kyc_data = null;
        } else {
            $advertiser->kv = Status::KYC_VERIFIED;
        }
        $advertiser->save();

        $notify[] = ['success', 'Advertiser details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id) {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act'    => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $advertiser = Advertiser::findOrFail($id);
        $amount     = $request->amount;
        $trx        = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $advertiser->balance += $amount;

            $transaction->trx_type = '+';
            $transaction->remark   = 'balance_add';

            $notifyTemplate = 'BAL_ADD';

            $notify[] = ['success', 'Balance added successfully'];

        } else {
            if ($amount > $advertiser->balance) {
                $notify[] = ['error', $advertiser->username . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $advertiser->balance -= $amount;

            $transaction->trx_type = '-';
            $transaction->remark   = 'balance_subtract';

            $notifyTemplate = 'BAL_SUB';
            $notify[]       = ['success', 'Balance subtracted successfully'];
        }

        $advertiser->save();

        $transaction->advertiser_id = $advertiser->id;
        $transaction->amount        = $amount;
        $transaction->post_balance  = $advertiser->balance;
        $transaction->charge        = 0;
        $transaction->trx           = $trx;
        $transaction->details       = $request->remark;
        $transaction->save();

        notify($advertiser, $notifyTemplate, [
            'trx'          => $trx,
            'amount'       => showAmount($amount, currencyFormat: false),
            'remark'       => $request->remark,
            'post_balance' => showAmount($advertiser->balance, currencyFormat: false),
        ]);

        return back()->withNotify($notify);
    }

    public function login($id) {
        Auth::logout();
        Auth::guard('advertiser')->loginUsingId($id);
        return to_route('advertiser.home');
    }

    public function status(Request $request, $id) {
        $advertiser = Advertiser::findOrFail($id);
        if ($advertiser->status == Status::ADVERTISER_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:255',
            ]);
            $advertiser->status     = Status::ADVERTISER_BAN;
            $advertiser->ban_reason = $request->reason;
            $notify[]               = ['success', 'Advertiser banned successfully'];
        } else {
            $advertiser->status     = Status::ADVERTISER_ACTIVE;
            $advertiser->ban_reason = null;
            $notify[]               = ['success', 'Advertiser unbanned successfully'];
        }
        $advertiser->save();
        return back()->withNotify($notify);

    }

    public function showNotificationSingleForm($id) {
        $advertiser = Advertiser::findOrFail($id);
        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.advertisers.detail', $advertiser->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $advertiser->username;
        return view('admin.advertisers.notification_single', compact('pageTitle', 'advertiser'));
    }

    public function sendNotificationSingle(Request $request, $id) {
        $request->validate([
            'message' => 'required',
            'via'     => 'required|in:email,sms,push',
            'subject' => 'required_if:via,email,push',
            'image'   => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        return (new AdvertiserNotificationSender())->notificationToSingle($request, $id);
    }

    public function showNotificationAllForm() {
        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $notifyToAdvertiser = Advertiser::notifyToAdvertiser();
        $advertisers        = Advertiser::active()->count();
        $pageTitle          = 'Notification to Verified Advertisers';

        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }

        return view('admin.advertisers.notification_all', compact('pageTitle', 'advertisers', 'notifyToAdvertiser'));
    }

    public function sendNotificationAll(Request $request) {
        $request->validate([
            'via'                                => 'required|in:email,sms,push',
            'message'                            => 'required',
            'subject'                            => 'required_if:via,email,push',
            'start'                              => 'required|integer|gte:1',
            'batch'                              => 'required|integer|gte:1',
            'being_sent_to'                      => 'required',
            'cooling_time'                       => 'required|integer|gte:1',
            'number_of_top_deposited_advertiser' => 'required_if:being_sent_to,topDepositedAdvertisers|integer|gte:0',
            'number_of_days'                     => 'required_if:being_sent_to,notLoginAdvertisers|integer|gte:0',
            'image'                              => ["nullable", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'number_of_days.required_if'                     => "Number of days field is required",
            'number_of_top_deposited_advertiser.required_if' => "Number of top deposited advertiser field is required",
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        return (new AdvertiserNotificationSender())->notificationToAll($request);
    }

    public function countBySegment($methodName) {
        return Advertiser::active()->$methodName()->count();
    }

    public function list() {
        $query = Advertiser::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $advertisers = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success'     => true,
            'advertisers' => $advertisers,
            'more'        => $advertisers->hasMorePages(),
        ]);
    }

    public function notificationLog($id) {
        $advertiser = Advertiser::findOrFail($id);
        $pageTitle  = 'Notifications Sent to ' . $advertiser->username;
        $logs       = NotificationLog::where('advertiser_id', $id)->with('advertiser')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'advertiser'));
    }

}
