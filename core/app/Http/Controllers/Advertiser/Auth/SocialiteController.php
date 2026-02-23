<?php

namespace App\Http\Controllers\Advertiser\Auth;

use App\Http\Controllers\Controller;
use App\Lib\AdvertiserSocialLogin;

class SocialiteController extends Controller {

    public function socialLogin($provider) {
        $socialLogin = new AdvertiserSocialLogin($provider);
        return $socialLogin->redirectDriver();
    }

    public function callback($provider) {
        $socialLogin = new AdvertiserSocialLogin($provider);
        try {
            return $socialLogin->login();
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return to_route('home')->withNotify($notify);
        }
    }
}
