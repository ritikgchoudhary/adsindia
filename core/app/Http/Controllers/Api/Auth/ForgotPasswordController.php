<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetCodeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        if (!gs('en')) {
            return responseError('email_disabled', ['Email service is currently disabled. Please contact customer support to reset your password.']);
        }

        $user = User::where('email', strtolower($request->email))->first();
        if (!$user) {
            return responseError('user_not_found', ['The account could not be found']);
        }

        PasswordReset::where('email', $user->email)->delete();

        $code = verificationCode(6);
        $reset = new PasswordReset();
        $reset->email = $user->email;
        $reset->token = $code;
        $reset->created_at = Carbon::now();
        $reset->save();

        // Send code email
        $userIpInfo = getIpInfo();
        $userBrowserInfo = osBrowser();
        notify($user, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => $userBrowserInfo['os_platform'] ?? '',
            'browser' => $userBrowserInfo['browser'] ?? '',
            'ip' => $userIpInfo['ip'] ?? '',
            'time' => $userIpInfo['time'] ?? '',
        ], ['email'], createLog: false);

        return responseSuccess('password_reset_code_sent', ['Verification code sent to your email. If you don\'t receive it, please contact customer support.'], [
            'email' => $user->email,
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        $code = str_replace(' ', '', (string) $request->code);
        $exists = PasswordReset::where('token', $code)
            ->where('email', strtolower($request->email))
            ->exists();

        if (!$exists) {
            return responseError('invalid_code', ['Verification code doesn\'t match']);
        }

        return responseSuccess('code_verified', ['Code verified successfully']);
    }

    public function reset(Request $request)
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string',
            'password' => ['required', 'confirmed', $passwordValidation],
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        $code = str_replace(' ', '', (string) $request->code);
        $reset = PasswordReset::where('token', $code)
            ->where('email', strtolower($request->email))
            ->orderByDesc('created_at')
            ->first();

        if (!$reset) {
            return responseError('invalid_code', ['Invalid verification code']);
        }

        $user = User::where('email', $reset->email)->first();
        if (!$user) {
            return responseError('user_not_found', ['The account could not be found']);
        }

        // IMPORTANT: API auth uses plain password comparison in LoginController.
        $user->password = (string) $request->password;
        $user->save();

        PasswordReset::where('email', $user->email)->delete();

        // Notify user password reset done
        if (gs('en')) {
            $userIpInfo = getIpInfo();
            $userBrowserInfo = osBrowser();
            notify($user, 'PASS_RESET_DONE', [
                'operating_system' => $userBrowserInfo['os_platform'] ?? '',
                'browser' => $userBrowserInfo['browser'] ?? '',
                'ip' => $userIpInfo['ip'] ?? '',
                'time' => $userIpInfo['time'] ?? '',
            ], ['email'], createLog: false);
        }

        return responseSuccess('password_reset_done', ['Password changed successfully. Please login with your new password.']);
    }
}
