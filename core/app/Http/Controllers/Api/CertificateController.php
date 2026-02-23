<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CoursePlanOrder;
use App\Models\Transaction;
use App\Models\UserCertificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    private const AD_CERT_PRICE = 1250.0;
    private const AD_CERT_REMARK = 'ad_certificate_fee';

    private function hasAdCertificate($user): bool
    {
        return (bool) $user->has_ad_certificate_view;
    }

    /**
     * Certificates: list of courses (by user's plan). Locked until course completed, then view/download.
     */
    public function getCertificates()
    {
        $user = auth()->user();
        if ($user) $user->refresh();
        $hasAdCertificate = $this->hasAdCertificate($user);

        $order = CoursePlanOrder::where('user_id', $user->id)
            ->active()
            ->with('plan')
            ->get()
            ->filter(fn ($o) => $o->plan !== null)
            ->sortByDesc(fn ($o) => (int) $o->plan->level)
            ->first();

        // If no active plan, check latest order for package indication
        if (!$order) {
            $order = CoursePlanOrder::where('user_id', $user->id)
                ->with('plan')
                ->orderByDesc('id')
                ->first();
        }

        $userPlanLevel = $order && $order->plan ? (int) $order->plan->level : 0;

        $courses = Course::active()
            ->ordered()
            ->get();

        $list = [];
        foreach ($courses as $course) {
            $requiredPlan = (int) ($course->required_course_plan_id ?? 1);
            if ($userPlanLevel < $requiredPlan) {
                continue;
            }

            $userCert = UserCertificate::where('user_id', $user->id)->where('course_id', $course->id)->first();

            $list[] = [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'course_slug' => $course->slug,
                'locked' => $userCert === null || !$hasAdCertificate,
                'certificate_id' => $userCert ? $userCert->id : null,
                'certificate_number' => $userCert ? $userCert->certificate_number : null,
                'issued_at' => $userCert && $userCert->issued_at ? $userCert->issued_at->format('Y-m-d H:i:s') : null,
                'name' => $userCert ? ($course->name . ' – Certificate') : $course->name,
            ];
        }

        return responseSuccess('certificates', ['Certificates retrieved successfully'], [
            'data' => $list,
            'requires_ad_certificate' => true,
            'has_ad_certificate' => $hasAdCertificate,
            'ad_certificate_price' => (float) self::AD_CERT_PRICE,
        ]);
    }

    /**
     * Get single certificate (for view/download). Only own certificates.
     */
    public function getCertificate($id)
    {
        $user = auth()->user();
        if ($user) $user->refresh();
        if (!$this->hasAdCertificate($user)) {
            return responseError('ad_certificate_required', ['Please purchase Ad Certificate (₹1250) to unlock certificates.']);
        }
        $uc = UserCertificate::where('user_id', $user->id)->where('id', $id)->with('course')->first();
        if (!$uc) {
            return responseError('certificate_not_found', ['Certificate not found']);
        }
        return responseSuccess('certificate', ['Certificate retrieved successfully'], [
            'id' => $uc->id,
            'course_id' => $uc->course_id,
            'course_name' => $uc->course ? $uc->course->name : 'Course',
            'certificate_number' => $uc->certificate_number,
            'issued_at' => $uc->issued_at ? $uc->issued_at->format('Y-m-d H:i:s') : null,
            'name' => ($uc->course ? $uc->course->name : 'Course') . ' – Certificate',
        ]);
    }
}
