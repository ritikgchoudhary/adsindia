<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserCertificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * Certificates earned by completing courses (no payment – complete course = get certificate).
     */
    public function getCertificates()
    {
        $user = auth()->user();

        $certificates = UserCertificate::where('user_id', $user->id)
            ->with('course')
            ->orderByDesc('issued_at')
            ->get()
            ->map(function ($uc) {
                return [
                    'id' => $uc->id,
                    'course_id' => $uc->course_id,
                    'name' => $uc->course ? $uc->course->name . ' – Certificate' : 'Course Certificate',
                    'description' => $uc->course ? 'Awarded on completion of: ' . $uc->course->name : 'Course completion certificate',
                    'certificate_number' => $uc->certificate_number,
                    'issued_at' => $uc->issued_at->format('Y-m-d H:i:s'),
                    'course_title' => $uc->course ? $uc->course->name : null,
                ];
            });

        return responseSuccess('certificates', ['Certificates retrieved successfully'], [
            'data' => $certificates,
        ]);
    }
}
