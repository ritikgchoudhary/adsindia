<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLogin;
use App\Constants\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        // Determine if username is email or username
        $login = $request->username;
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Find user
        $user = User::where($fieldType, $login)->first();

        if (!$user) {
            return responseError('invalid_credentials', ['Invalid username or password']);
        }

        // Check if user is banned
        if ($user->status == Status::USER_BAN) {
            return responseError('account_banned', ['Your account has been banned']);
        }

        // Check plain password (system stores plain passwords)
        if ($user->password !== $request->password) {
            return responseError('invalid_credentials', ['Invalid username or password']);
        }

        // Create Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        // Log login activity
        $this->logUserLogin($user);

        // Return user data and token
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullname' => $user->fullname,
            'mobile' => $user->mobile,
            'dial_code' => $user->dial_code,
            'status' => $user->status,
            'ev' => $user->ev,
            'sv' => $user->sv,
            'kv' => $user->kv,
        ];

        return responseSuccess('login_successful', ['Login successful'], [
            'user' => $userData,
            'token' => $token,
        ]);
    }

    public function checkToken(Request $request)
    {
        // This should be called with auth:sanctum middleware
        // But if called without, we'll check the token manually
        $user = $request->user();
        
        if (!$user) {
            // Try to get user from token manually
            $token = $request->bearerToken();
            if ($token) {
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken) {
                    $user = $personalAccessToken->tokenable;
                }
            }
        }
        
        if (!$user) {
            return responseError('invalid_token', ['Invalid or expired token']);
        }

        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'fullname' => $user->fullname,
            'mobile' => $user->mobile,
            'dial_code' => $user->dial_code,
            'status' => $user->status,
            'ev' => $user->ev,
            'sv' => $user->sv,
            'kv' => $user->kv,
        ];

        return responseSuccess('token_valid', ['Token is valid'], [
            'user' => $userData,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            // Revoke current token
            $request->user()->currentAccessToken()?->delete();
            
            return responseSuccess('logout_successful', ['Logout successful']);
        }
        
        return responseError('not_authenticated', ['User not authenticated']);
    }

    public function socialLogin(Request $request)
    {
        // TODO: Implement social login (Google, Facebook, etc.)
        return responseError('not_implemented', ['Social login not yet implemented']);
    }

    /**
     * Log user login activity
     */
    private function logUserLogin($user)
    {
        try {
            $ip = getRealIP();
            $exist = UserLogin::where('user_ip', $ip)->first();
            $userLogin = new UserLogin();

            if ($exist) {
                $userLogin->longitude = $exist->longitude;
                $userLogin->latitude = $exist->latitude;
                $userLogin->city = $exist->city;
                $userLogin->country_code = $exist->country_code;
                $userLogin->country = $exist->country;
            } else {
                $info = json_decode(json_encode(getIpInfo()), true);
                $userLogin->longitude = isset($info['long']) ? implode(',', $info['long']) : '';
                $userLogin->latitude = isset($info['lat']) ? implode(',', $info['lat']) : '';
                $userLogin->city = isset($info['city']) ? implode(',', $info['city']) : '';
                $userLogin->country_code = isset($info['code']) ? implode(',', $info['code']) : '';
                $userLogin->country = isset($info['country']) ? implode(',', $info['country']) : '';
            }

            $userAgent = osBrowser();
            $userLogin->user_id = $user->id;
            $userLogin->user_ip = $ip;
            $userLogin->browser = isset($userAgent['browser']) ? $userAgent['browser'] : '';
            $userLogin->os = isset($userAgent['os_platform']) ? $userAgent['os_platform'] : '';
            $userLogin->save();
        } catch (\Exception $e) {
            // Log error but don't fail login
            \Log::error('Failed to log user login: ' . $e->getMessage());
        }
    }
}
