<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\AdPackageOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        if (!gs('registration')) {
            return responseError('registration_disabled', ['Registration is currently disabled']);
        }

        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|min:6|max:255|unique:users|regex:/^[a-z0-9_]+$/',
            'password' => ['required', 'confirmed', $passwordValidation],
            'mobile' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:10',
            'ref' => 'nullable|string',
            'pkg' => 'nullable|integer|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return responseError('validation_failed', $validator->errors());
        }

        // Handle referral
        $referUser = null;
        if ($request->ref) {
            $referUser = User::where('username', $request->ref)->first();
        }

        // Handle package discount
        $packageId = $request->pkg;
        $packageDiscount = 0;
        $packagePrice = 0;
        
        if ($packageId) {
            $packages = [
                1 => ['name' => 'AdsLite', 'price' => 1499, 'discount' => 0],
                2 => ['name' => 'AdsPro', 'price' => 2999, 'discount' => 499],
                3 => ['name' => 'AdsSupreme', 'price' => 5999, 'discount' => 0],
                4 => ['name' => 'AdsPremium', 'price' => 9999, 'discount' => 0],
                5 => ['name' => 'AdsPremium+', 'price' => 15999, 'discount' => 0],
            ];
            
            if (isset($packages[$packageId])) {
                $packageDiscount = $packages[$packageId]['discount'];
                $packagePrice = $packages[$packageId]['price'] - $packageDiscount;
            }
        }

        // Create user
        $user = new User();
        $user->email = strtolower($request->email);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->username = strtolower($request->username);
        $user->password = $request->password; // Store as plain text as per existing logic
        $user->mobile = $request->mobile ?? '';
        $user->country_code = $request->country_code ?? '';
        $user->ref_by = $referUser ? $referUser->id : 0;
        $user->kv = gs('kv') ? Status::KYC_UNVERIFIED : Status::KYC_VERIFIED;
        $user->ev = gs('ev') ? Status::UNVERIFIED : Status::VERIFIED;
        $user->sv = gs('sv') ? Status::UNVERIFIED : Status::VERIFIED;
        $user->ts = Status::DISABLE;
        $user->tv = Status::ENABLE;
        $user->status = Status::USER_ACTIVE;
        $user->save();

        // Create admin notification
        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();

        // Login log
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

        // If package selected, create order (user needs to pay)
        if ($packageId && $packagePrice > 0) {
            // Store package info for payment
            $user->pending_package_id = $packageId;
            $user->pending_package_price = $packagePrice;
            $user->save();
        }

        // Give 2 free ads on registration (as per requirements)
        if ($packageId) {
            // Create a temporary package order for 2 free ads
            $freePackageOrder = AdPackageOrder::create([
                'user_id' => $user->id,
                'package_id' => $packageId,
                'amount' => 0,
                'status' => 1,
                'expires_at' => now()->addDays(7), // 7 days validity for free ads
            ]);
        }

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return responseSuccess('registration_successful', ['Registration successful'], [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
            'token' => $token,
            'package_info' => $packageId ? [
                'package_id' => $packageId,
                'original_price' => $packages[$packageId]['price'],
                'discount' => $packageDiscount,
                'discounted_price' => $packagePrice,
                'message' => $packagePrice > 0 ? 'Please pay ₹' . number_format($packagePrice, 2) . ' to activate your package' : 'Package activated successfully',
            ] : null,
        ]);
    }
}
