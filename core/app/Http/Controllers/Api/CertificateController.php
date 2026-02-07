<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function getCertificates()
    {
        $user = auth()->user();
        $general = gs();

        // Mock certificates - Replace with actual certificates from database
        $certificates = [
            [
                'id' => 1,
                'name' => 'Video Editing Certificate',
                'description' => 'Professional video editing certification',
                'price' => 1250,
                'is_applied' => false,
            ],
            [
                'id' => 2,
                'name' => 'Graphic Design Certificate',
                'description' => 'Professional graphic design certification',
                'price' => 1250,
                'is_applied' => false,
            ],
            [
                'id' => 3,
                'name' => 'Digital Marketing Certificate',
                'description' => 'Professional digital marketing certification',
                'price' => 1250,
                'is_applied' => false,
            ],
        ];

        return responseSuccess('certificates', ['Certificates retrieved successfully'], [
            'data' => $certificates,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    public function applyCertificate(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'certificate_id' => 'required|integer',
        ]);

        $certificatePrice = 1250; // Fixed 1250 fee as per requirements

        if ($user->balance < $certificatePrice) {
            return responseError('insufficient_balance', ['Insufficient balance']);
        }

        // Deduct balance
        $user->balance -= $certificatePrice;
        $user->save();

        // Create certificate application record
        // Add your certificate application logic here

        return responseSuccess('certificate_applied', ['Certificate application submitted successfully']);
    }
}
