<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Advertiser;
use App\Models\Provider;
use App\Models\UserLogin;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Socialite;

class AdvertiserSocialLogin {
    private $advertiser;
    private $fromApi;

    public function __construct($advertiser, $fromApi = false) {
        $this->advertiser = $advertiser;
        $this->fromApi    = $fromApi;
        $this->configuration();
    }

    public function redirectDriver() {
        return Socialite::driver($this->advertiser)->redirect();
    }

    private function configuration() {
        $advertiser    = $this->advertiser;
        $configuration = gs('socialite_credentials')->$advertiser;
        $advertiser    = $this->fromApi && $advertiser == 'linkedin' ? 'linkedin-openid' : $advertiser;

        Config::set('services.' . $advertiser, [
            'client_id'     => $configuration->client_id,
            'client_secret' => $configuration->client_secret,
            'redirect'      => route('provider.social.login.callback', $advertiser),
        ]);
    }

    public function login() {
        $advertiser = $this->advertiser;
        $advertiser = $this->fromApi && $advertiser == 'linkedin' ? 'linkedin-openid' : $advertiser;
        $driver     = Socialite::driver($advertiser);
        if ($this->fromApi) {
            try {
                $user = (object) $driver->userFromToken(request()->token)->user;
            } catch (\Throwable $th) {
                throw new Exception('Something went wrong');
            }
        } else {
            $user = $driver->user();
        }

        if ($advertiser == 'linkedin-openid') {
            $user->id = $user->sub;
        }

        $userData = Advertiser::where('provider_id', $user->id)->first();

        if (!$userData) {
            if (!gs('registration')) {
                throw new Exception('New account registration is currently disabled');
            }
            $emailExists = Advertiser::where('email', (isset($user->email) ? $user->email : ''))->exists();
            if ($emailExists) {
                throw new Exception('Email already exists');
            }

            $userData = $this->createUser($user, $this->advertiser);
        }
        if ($this->fromApi) {
            $tokenResult = $userData->createToken('auth_token')->plainTextToken;
            $this->loginLog($userData);
            return [
                'user'         => $userData,
                'access_token' => $tokenResult,
                'token_type'   => 'Bearer',
            ];
        }
        Auth::guard('advertiser')->login($user);
        $this->loginLog($userData);
        $redirection = Intended::getRedirection();
        return $redirection ? $redirection : to_route('advertiser.home');
    }

    private function createUser($user, $advertiser) {
        $general  = gs();
        $password = getTrx(8);

        $firstName = null;
        $lastName  = null;

        if (isset($user->first_name) && $user->first_name) {
            $firstName = $user->first_name;
        }
        if (isset($user->last_name) && $user->last_name) {
            $lastName = $user->last_name;
        }

        if ((!$firstName || !$lastName) && (isset($user->name) && $user->name)) {
            $firstName = preg_replace('/\W\w+\s*(\W*)$/', '$1', $user->name);
            $pieces    = explode(' ', $user->name);
            $lastName  = array_pop($pieces);
        }

        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = Advertiser::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }

        $newUser              = new Advertiser();
        $newUser->provider_id = $user->id;

        $newUser->email = $user->email;

        $newUser->password  = Hash::make($password);
        $newUser->firstname = $firstName;
        $newUser->lastname  = $lastName;
        $user->ref_by       = $referUser ? $referUser->id : 0;

        $newUser->status   = Status::VERIFIED;
        $newUser->kv       = $general->advertiser_kv ? Status::NO : Status::YES;
        $newUser->ev       = Status::VERIFIED;
        $newUser->sv       = gs('sv') ? Status::UNVERIFIED : Status::VERIFIED;
        $newUser->ts       = Status::DISABLE;
        $newUser->tv       = Status::VERIFIED;
        $newUser->provider = $advertiser;
        $newUser->save();

        $adminNotification                = new AdminNotification();
        $adminNotification->advertiser_id = $newUser->id;
        $adminNotification->title         = 'New member registered';
        $adminNotification->click_url     = urlPath('admin.advertisers.detail', $newUser->id);
        $adminNotification->save();

        $user = Advertiser::find($newUser->id);

        return $user;
    }

    private function loginLog($user) {
        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        //Check exist or not
        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = isset($info['long']) ? implode(',', $info['long']) : '';
            $userLogin->latitude     = isset($info['lat']) ? implode(',', $info['lat']) : '';
            $userLogin->city         = isset($info['city']) ? implode(',', $info['city']) : '';
            $userLogin->country_code = isset($info['code']) ? implode(',', $info['code']) : '';
            $userLogin->country      = isset($info['country']) ? implode(',', $info['country']) : '';
        }

        $userAgent              = osBrowser();
        $userLogin->provider_id = $user->id;
        $userLogin->user_ip     = $ip;

        $userLogin->browser = isset($userAgent['browser']) ? $userAgent['browser'] : '';
        $userLogin->os      = isset($userAgent['os_platform']) ? $userAgent['os_platform'] : '';
        $userLogin->save();
    }
}
