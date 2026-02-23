<?php

namespace App\Http\Controllers\Advertiser\Auth;

use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\AdvertiserLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Status;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    use AuthenticatesUsers;


    protected $username;


    public function __construct()
    {
        parent::__construct();
        $this->username = $this->findUsername();
    }

    public function showLoginForm()
    {
        $pageTitle = "Login";
        Intended::identifyRoute();
        return view('Template::advertiser.auth.login', compact('pageTitle'));
    }

    protected function guard()
    {
        return Auth::guard('advertiser');
    }

    public function login(Request $request)
    {

        $this->validateLogin($request);

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the advertiser back to the login form. Of course, when this
        // advertiser surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        Intended::reAssignSession();

        return $this->sendFailedLoginResponse($request);
    }

    public function findUsername()
    {
        $login = request()->input('username');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }

    public function username()
    {
        return $this->username;
    }

    protected function validateLogin($request)
    {

        $validator = Validator::make($request->all(), [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            Intended::reAssignSession();
            $validator->validate();
        }
    }

    /**
     * Attempt to log the advertiser into the application with plain password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $advertiser = \App\Models\Advertiser::where($this->username(), $credentials[$this->username()])->first();
        
        if (!$advertiser) {
            return false;
        }
        
        // Check plain password instead of hashed
        if ($advertiser->password === $credentials['password']) {
            $this->guard()->login($advertiser, $request->filled('remember'));
            return true;
        }
        
        return false;
    }

    public function logout()
    {
        $this->guard()->logout();
        request()->session()->invalidate();

        $notify[] = ['success', 'You have been logged out.'];
        return to_route('advertiser.login')->withNotify($notify);
    }


    public function authenticated(Request $request, $advertiser)
    {
        $advertiser->tv = $advertiser->ts == Status::VERIFIED ? Status::UNVERIFIED : Status::VERIFIED;
        $advertiser->save();
        $ip = getRealIP();
        $exist = AdvertiserLogin::where('advertiser_ip', $ip)->first();
        $advertiserLogin = new AdvertiserLogin();
        if ($exist) {
            $advertiserLogin->longitude =  $exist->longitude;
            $advertiserLogin->latitude =  $exist->latitude;
            $advertiserLogin->city =  $exist->city;
            $advertiserLogin->country_code = $exist->country_code;
            $advertiserLogin->country =  $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $advertiserLogin->longitude =  isset($info['long']) ? implode(',', $info['long']) : '';
            $advertiserLogin->latitude =  isset($info['lat']) ? implode(',', $info['lat']) : '';
            $advertiserLogin->city =  isset($info['city']) ? implode(',', $info['city']) : '';
            $advertiserLogin->country_code = isset($info['code']) ? implode(',', $info['code']) : '';
            $advertiserLogin->country =  isset($info['country']) ? implode(',', $info['country']) : '';
        }

        $advertiserAgent = osBrowser();
        $advertiserLogin->advertiser_id = $advertiser->id;
        $advertiserLogin->advertiser_ip =  $ip;

        $advertiserLogin->browser = isset($advertiserAgent['browser']) ? $advertiserAgent['browser'] : '';
        $advertiserLogin->os = isset($advertiserAgent['os_platform']) ? $advertiserAgent['os_platform'] : '';
        $advertiserLogin->save();

        auth()->logout();

        $redirection = Intended::getRedirection();
        return $redirection ? $redirection : to_route('advertiser.home');
    }
}
