<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin) {
            return responseError('invalid_credentials', ['Invalid username or password']);
        }

        // Check password (assuming plain text or hashed)
        if (!Hash::check($request->password, $admin->password) && $admin->password !== $request->password) {
            return responseError('invalid_credentials', ['Invalid username or password']);
        }

        // Create Sanctum token
        $token = $admin->createToken('admin-api-token')->plainTextToken;

        return responseSuccess('login_success', ['Login successful'], [
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'username' => $admin->username,
                'email' => $admin->email,
            ]
        ]);
    }
}
