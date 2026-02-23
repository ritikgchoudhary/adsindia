<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller {
    public function profile() {
        $pageTitle  = "Profile Setting";
        $advertiser = auth()->guard('advertiser')->user();
        return view('Template::advertiser.profile_setting', compact('pageTitle', 'advertiser'));
    }

    public function submitProfile(Request $request) {
        $request->validate([
            'firstname' => 'required|string',
            'lastname'  => 'required|string',
            'image'     => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'firstname.required' => 'The first name field is required',
            'lastname.required'  => 'The last name field is required',
        ]);

        $advertiser = auth()->guard('advertiser')->user();

        if ($request->hasFile('image')) {
            try {
                $advertiser->image = fileUploader($request->file('image'), getFilePath('advertiserProfile'), getFileSize('advertiserProfile'), $advertiser->image);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload your image'];
                return back()->withNotify($notify);
            }
        }

        $advertiser->firstname = $request->firstname;
        $advertiser->lastname  = $request->lastname;
        $advertiser->address   = $request->address;
        $advertiser->city      = $request->city;
        $advertiser->state     = $request->state;
        $advertiser->zip       = $request->zip;

        $advertiser->save();

        $notify[] = ['success', 'Profile updated successfully'];
        return back()->withNotify($notify);
    }

    public function changePassword() {
        $pageTitle = 'Change Password';
        return view('Template::advertiser.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request) {

        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', $passwordValidation],
        ]);

        $advertiser = auth()->guard('advertiser')->user();
        if (Hash::check($request->current_password, $advertiser->password)) {
            $password             = Hash::make($request->password);
            $advertiser->password = $password;
            $advertiser->save();
            $notify[] = ['success', 'Password changed successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }
    }
}
