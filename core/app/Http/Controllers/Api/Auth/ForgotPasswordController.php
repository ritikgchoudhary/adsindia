<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function sendResetCodeEmail(Request $request)
    {
        // TODO: Implement password reset email
        return responseError('not_implemented', ['Password reset email not yet implemented']);
    }

    public function verifyCode(Request $request)
    {
        // TODO: Implement code verification
        return responseError('not_implemented', ['Code verification not yet implemented']);
    }

    public function reset(Request $request)
    {
        // TODO: Implement password reset
        return responseError('not_implemented', ['Password reset not yet implemented']);
    }
}
