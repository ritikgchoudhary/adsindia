<?php

namespace App\Http\Controllers\Advertiser;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Lib\Intended;

class AuthorizationController extends Controller
{
    protected function checkCodeValidity($advertiser, $addMin = 2)
    {
        if (!$advertiser->ver_code_send_at) {
            return false;
        }
        if ($advertiser->ver_code_send_at->addMinutes($addMin) < Carbon::now()) {
            return false;
        }
        return true;
    }

    public function authorizeForm()
    {
        $advertiser = auth()->guard('advertiser')->user();
        if (!$advertiser->status) {
            $pageTitle = 'Banned';
            $type = 'ban';
        } elseif (!$advertiser->ev) {
            $type = 'email';
            $pageTitle = 'Verify Email';
            $notifyTemplate = 'EVER_CODE';
        } elseif (!$advertiser->sv) {
            $type = 'sms';
            $pageTitle = 'Verify Mobile Number';
            $notifyTemplate = 'SVER_CODE';
        } elseif (!$advertiser->tv) {
            $pageTitle = '2FA Verification';
            $type = '2fa';
        } else {
            return to_route('advertiser.home');
        }

        if (!$this->checkCodeValidity($advertiser) && ($type != '2fa') && ($type != 'ban')) {
            $advertiser->ver_code = verificationCode(6);
            $advertiser->ver_code_send_at = Carbon::now();
            $advertiser->save();
            notify($advertiser, $notifyTemplate, [
                'code' => $advertiser->ver_code
            ], [$type]);
        }

        return view('Template::advertiser.auth.authorization.' . $type, compact('advertiser', 'pageTitle'));
    }

    public function sendVerifyCode($type)
    {
        $advertiser = auth()->guard('advertiser')->user();

        if ($this->checkCodeValidity($advertiser)) {
            $targetTime = $advertiser->ver_code_send_at->addMinutes(2)->timestamp;
            $delay = $targetTime - time();
            throw ValidationException::withMessages(['resend' => 'Please try after ' . $delay . ' seconds']);
        }

        $advertiser->ver_code = verificationCode(6);
        $advertiser->ver_code_send_at = Carbon::now();
        $advertiser->save();

        if ($type == 'email') {
            $type = 'email';
            $notifyTemplate = 'EVER_CODE';
        } else {
            $type = 'sms';
            $notifyTemplate = 'SVER_CODE';
        }

        notify($advertiser, $notifyTemplate, [
            'code' => $advertiser->ver_code
        ], [$type]);

        $notify[] = ['success', 'Verification code sent successfully'];
        return back()->withNotify($notify);
    }

    public function emailVerification(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $advertiser = auth()->guard('advertiser')->user();

        if ($advertiser->ver_code == $request->code) {
            $advertiser->ev = Status::VERIFIED;
            $advertiser->ver_code = null;
            $advertiser->ver_code_send_at = null;
            $advertiser->save();

            $redirection = Intended::getRedirection();
            return $redirection ? $redirection : to_route('advertiser.home');
        }
        throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
    }

    public function mobileVerification(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);


        $advertiser = auth()->guard('advertiser')->user();
        if ($advertiser->ver_code == $request->code) {
            $advertiser->sv = Status::VERIFIED;
            $advertiser->ver_code = null;
            $advertiser->ver_code_send_at = null;
            $advertiser->save();
            $redirection = Intended::getRedirection();
            return $redirection ? $redirection : to_route('advertiser.home');
        }
        throw ValidationException::withMessages(['code' => 'Verification code didn\'t match!']);
    }

    public function g2faVerification(Request $request)
    {
        $advertiser = auth()->guard('advertiser')->user();
        $request->validate([
            'code' => 'required',
        ]);
        $response = verifyG2fa($advertiser, $request->code);
        if ($response) {
            $redirection = Intended::getRedirection();
            return $redirection ? $redirection : to_route('advertiser.home');
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }
}
