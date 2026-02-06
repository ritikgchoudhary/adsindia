<?php

namespace App\Http\Controllers\Advertiser\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\AdminNotification;
use App\Models\Advertiser;
use App\Models\AdvertiserLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller {

    use RegistersUsers;

    public function __construct() {
        parent::__construct();
    }

    protected function guard() {
        return Auth::guard('advertiser');
    }

    public function showRegistrationForm() {
        $pageTitle = "Register";
        Intended::identifyRoute();
        return view('Template::advertiser.auth.register', compact('pageTitle'));
    }

    protected function validator(array $data) {

        $passwordValidation = Password::min(6);

        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $agree = 'nullable';
        if (gs('agree')) {
            $agree = 'required';
        }

        $validate = Validator::make($data, [
            'firstname' => 'required',
            'lastname'  => 'required',
            'email'     => 'required|string|email|unique:advertisers',
            'password'  => ['required', 'confirmed', $passwordValidation],
            'captcha'   => 'sometimes|required',
            'agree'     => $agree,
        ], [
            'firstname.required' => 'The first name field is required',
            'lastname.required'  => 'The last name field is required',
        ]);

        return $validate;
    }

    public function register(Request $request) {
        if (!gs('registration')) {
            $notify[] = ['error', 'Registration not allowed'];
            return back()->withNotify($notify);
        }
        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        event(new Registered($advertiser = $this->create($request->all())));

        $this->guard()->login($advertiser);

        return $this->registered($request, $advertiser)
        ?: redirect($this->redirectPath());
    }

    protected function create(array $data) {
        $referBy = session()->get('reference');
        if ($referBy) {
            $referAdvertiser = Advertiser::where('username', $referBy)->first();
        } else {
            $referAdvertiser = null;
        }

        //Advertiser Create
        $advertiser            = new Advertiser();
        $advertiser->email     = strtolower($data['email']);
        $advertiser->firstname = $data['firstname'];
        $advertiser->lastname  = $data['lastname'];
        $advertiser->password  = $data['password']; // Store plain password
        $advertiser->ref_by    = $referAdvertiser ? $referAdvertiser->id : 0;
        $advertiser->kv        = gs('advertiser_kv') ? Status::NO : Status::YES;
        $advertiser->ev        = gs('ev') ? Status::NO : Status::YES;
        $advertiser->sv        = gs('sv') ? Status::NO : Status::YES;
        $advertiser->ts        = Status::DISABLE;
        $advertiser->tv        = Status::ENABLE;
        $advertiser->save();

        $adminNotification                = new AdminNotification();
        $adminNotification->advertiser_id = $advertiser->id;
        $adminNotification->title         = 'New member registered';
        $adminNotification->click_url     = urlPath('admin.advertisers.detail', $advertiser->id);
        $adminNotification->save();

        //Login Log Create
        $ip              = getRealIP();
        $exist           = AdvertiserLogin::where('advertiser_ip', $ip)->first();
        $advertiserLogin = new AdvertiserLogin();

        if ($exist) {
            $advertiserLogin->longitude    = $exist->longitude;
            $advertiserLogin->latitude     = $exist->latitude;
            $advertiserLogin->city         = $exist->city;
            $advertiserLogin->country_code = $exist->country_code;
            $advertiserLogin->country      = $exist->country;
        } else {
            $info                          = json_decode(json_encode(getIpInfo()), true);
            $advertiserLogin->longitude    = isset($info['long']) ? implode(',', $info['long']) : '';
            $advertiserLogin->latitude     = isset($info['lat']) ? implode(',', $info['lat']) : '';
            $advertiserLogin->city         = isset($info['city']) ? implode(',', $info['city']) : '';
            $advertiserLogin->country_code = isset($info['code']) ? implode(',', $info['code']) : '';
            $advertiserLogin->country      = isset($info['country']) ? implode(',', $info['country']) : '';
        }

        $advertiserAgent                = osBrowser();
        $advertiserLogin->advertiser_id = $advertiser->id;
        $advertiserLogin->advertiser_ip = $ip;

        $advertiserLogin->browser = isset($advertiserAgent['browser']) ? $advertiserAgent['browser'] : '';
        $advertiserLogin->os      = isset($advertiserAgent['os_platform']) ? $advertiserAgent['os_platform'] : '';
        $advertiserLogin->save();

        auth()->logout();

        return $advertiser;
    }

    public function checkAdvertiser(Request $request) {
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) {
            $exist['data']  = Advertiser::where('email', $request->email)->exists();
            $exist['type']  = 'email';
            $exist['field'] = 'Email';
        }
        if ($request->mobile) {
            $exist['data']  = Advertiser::where('mobile', $request->mobile)->where('dial_code', $request->mobile_code)->exists();
            $exist['type']  = 'mobile';
            $exist['field'] = 'Mobile';
        }
        if ($request->username) {
            $exist['data']  = Advertiser::where('username', $request->username)->exists();
            $exist['type']  = 'username';
            $exist['field'] = 'Username';
        }
        return response($exist);
    }

    public function registered() {
        return to_route('advertiser.home');
    }
}
