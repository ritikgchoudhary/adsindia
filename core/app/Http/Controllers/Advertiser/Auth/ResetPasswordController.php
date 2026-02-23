<?php

namespace App\Http\Controllers\Advertiser\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\Advertiser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token = null)
    {

        $email = session('fpass_email');
        $token = session()->has('token') ? session('token') : $token;
        if (PasswordReset::where('token', $token)->where('email', $email)->count() != 1) {
            $notify[] = ['error', 'Invalid token'];
            return to_route('advertiser.password.request')->withNotify($notify);
        }
        return view('Template::advertiser.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $email, 'pageTitle' => 'Reset Password']
        );
    }

    public function reset(Request $request)
    {
        $request->validate($this->rules());
        $reset = PasswordReset::where('token', $request->token)->orderBy('created_at', 'desc')->first();
        if (!$reset) {
            $notify[] = ['error', 'Invalid verification code'];
            return to_route('advertiser.login')->withNotify($notify);
        }

        $advertiser = Advertiser::where('email', $reset->email)->first();
        $advertiser->password = Hash::make($request->password);
        $advertiser->save();



        $advertiserIpInfo = getIpInfo();
        $advertiserBrowser = osBrowser();
        notify($advertiser, 'PASS_RESET_DONE', [
            'operating_system' => isset($advertiserBrowser['os_platform']) ? $advertiserBrowser['os_platform'] : '',
            'browser' => isset($advertiserBrowser['browser']) ? $advertiserBrowser['browser'] : '',
            'ip' => isset($advertiserIpInfo['ip']) ? $advertiserIpInfo['ip'] : '',
            'time' => isset($advertiserIpInfo['time']) ? $advertiserIpInfo['time'] : ''
        ],['email']);


        $notify[] = ['success', 'Password changed successfully'];
        return to_route('advertiser.login')->withNotify($notify);
    }


    protected function rules()
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required','confirmed',$passwordValidation],
        ];
    }

}
