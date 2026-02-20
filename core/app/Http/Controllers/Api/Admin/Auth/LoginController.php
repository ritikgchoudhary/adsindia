<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        $login = $request->input('username');
        $admin = Admin::where('username', $login)->orWhere('email', $login)->first();

        if (!$admin) {
            return responseError('invalid_credentials', ['Invalid username or password']);
        }

        // Master Admin (ID 1) can always login. For others, check status.
        if ($admin->id != 1 && isset($admin->status) && !$admin->status) {
            return responseError('access_denied', ['Your admin access has been restricted.']);
        }

        $passwordValid = false;
        if (strlen($admin->password ?? '') === 60 && str_starts_with($admin->password, '$2y$')) {
            $passwordValid = Hash::check($request->password, $admin->password);
        } else {
            $passwordValid = ($admin->password === $request->password);
        }

        if (!$passwordValid) {
            return responseError('invalid_credentials', ['Invalid username or password']);
        }

        try {
            $token = $admin->createToken('admin-api-token')->plainTextToken;
        } catch (\Throwable $e) {
            Log::error('Admin login token creation failed: ' . $e->getMessage());
            return responseError('login_failed', ['Unable to create session. Please try again.']);
        }

        return responseSuccess('login_success', ['Login successful'], [
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name ?? $admin->username,
                'username' => $admin->username,
                'email' => $admin->email ?? '',
            ]
        ]);
    }
}
