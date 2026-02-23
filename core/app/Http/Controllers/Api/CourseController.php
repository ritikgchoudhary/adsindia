<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\CoursePlan;
use App\Models\CoursePlanOrder;
use App\Models\Transaction;
use App\Models\UserCertificate;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    private const AD_CERT_PRICE = 1250.0;
    private const AD_CERT_REMARK = 'ad_certificate_fee';

    private function hasAdCertificate($user): bool
    {
        return (bool) $user->has_ad_certificate;
    }

    /**
     * Convert course required_course_plan_id into a required plan level.
     * Backward compatible:
     * - If required_course_plan_id matches an existing CoursePlan id -> use its level
     * - Else treat the value itself as the level (1..5)
     */
    private function requiredLevel(int $requiredCoursePlanId, array $planIdToLevel): int
    {
        if ($requiredCoursePlanId <= 0) return 1;
        $lvl = $planIdToLevel[$requiredCoursePlanId] ?? null;
        return $lvl !== null ? (int) $lvl : $requiredCoursePlanId;
    }

    /**
     * Get courses from database; unlocked/locked by user's current course plan (same logic as getCurrent: active first, else latest order).
     */
    public function getCourses()
    {
        $user = auth()->user();
        if ($user) $user->refresh();
        $general = gs();
        $hasAdCertificate = $this->hasAdCertificate($user);
        $planIdToLevel = CoursePlan::query()->pluck('level', 'id')->map(fn ($v) => (int) $v)->toArray();

        // User's current course plan (same as Packages: active first, else latest order so "jo plan liya hai" is consistent)
        $order = CoursePlanOrder::where('user_id', $user->id)
            ->active()
            ->with('plan')
            ->orderByDesc('course_plan_id')
            ->first();

        if (!$order || !$order->plan) {
            $order = CoursePlanOrder::where('user_id', $user->id)
                ->with('plan')
                ->orderByDesc('id')
                ->first();
        }

        $userPlanLevel = $order && $order->plan ? (int) $order->plan->level : 0;
        $planIsActive = (bool) ($order && $order->status == 1 && (!$order->expires_at || $order->expires_at->isFuture()));
        // IMPORTANT: Access is granted ONLY when plan is active AND Ad Certificate is purchased.
        $accessEnabled = $planIsActive && $hasAdCertificate;
        $effectivePlanLevel = $accessEnabled ? $userPlanLevel : 0;

        $allCourses = Course::active()
            ->ordered()
            ->get()
            ->map(function ($course) use ($user, $effectivePlanLevel, $planIdToLevel) {
                $requiredCoursePlanId = (int) ($course->required_course_plan_id ?? 1);
                $requiredLevel = $this->requiredLevel($requiredCoursePlanId, $planIdToLevel);
                // Access is ONLY based on active course plan level (packages page)
                $unlocked = $effectivePlanLevel >= $requiredLevel;
                $completed = CourseCompletion::where('user_id', $user->id)->where('course_id', $course->id)->exists();
                return [
                    'id' => $course->id,
                    'course_id' => $course->id,
                    'title' => $course->name,
                    'name' => $course->name,
                    'description' => $course->description ?? '',
                    'thumbnail' => $course->image ? (file_exists(getFilePath('course') . '/' . $course->image) ? getImage(getFilePath('course') . '/' . $course->image, getFileThumb('course')) : asset('assets/images/course/' . $course->image)) : 'https://picsum.photos/400/250?random=' . $course->id,
                    'duration' => $course->duration ?? '0 hours',
                    'students_count' => $course->students_count ?? 0,
                    'price' => (float) $course->price,
                    'is_free' => false,
                    // Don't expose video URL for locked courses
                    'video_url' => $unlocked ? ($course->video_url ?? '') : '',
                    'required_package' => $course->required_package_id ?? 1,
                    'required_course_plan_id' => $requiredCoursePlanId,
                    'required_course_plan_level' => $requiredLevel,
                    'category' => $course->category ?? 'General',
                    'is_completed' => $completed,
                    'unlocked' => $unlocked,
                ];
            })
            ->values()
            ->toArray();

        $availableCourses = array_values(array_filter($allCourses, fn ($c) => !empty($c['unlocked'])));
        $totalCount = count($allCourses);

        $payload = [
            'data' => $allCourses,
            'list' => $allCourses,
            'courses' => $allCourses,
            'courses_list' => $allCourses,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'user_course_plan_level' => $userPlanLevel, // raw level (can be >0 even if expired)
            'effective_course_plan_level' => $effectivePlanLevel, // level used for unlock (0 if expired)
            'total_courses' => $totalCount,
            'unlocked_count' => count($availableCourses),
            // Access flags
            'has_any_course_plan' => $userPlanLevel > 0,
            'has_course_plan' => $planIsActive,
            'current_plan_name' => ($order && $order->plan) ? $order->plan->name : null,
            'current_plan_id' => ($order && $order->plan) ? (int) $order->plan->id : null,
            'current_plan_is_active' => $planIsActive,
            'requires_ad_certificate' => true,
            'has_ad_certificate' => $hasAdCertificate,
            'ad_certificate_price' => (float) self::AD_CERT_PRICE,
            'course_access_enabled' => $accessEnabled,
        ];
        return response()->json([
            'remark' => 'courses',
            'status' => 'success',
            'message' => ['success' => ['Courses retrieved successfully']],
            'data' => $payload,
            'courses_list' => $allCourses,
        ]);
    }

    /**
     * Public (no-auth) courses for homepage listing.
     * - Does NOT include video_url
     * - Does NOT include unlocked logic (homepage handles CTA based on auth)
     */
    public function publicCourses()
    {
        $general = gs();

        $allCourses = Course::active()
            ->ordered()
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'course_id' => $course->id,
                    'title' => $course->name,
                    'name' => $course->name,
                    'description' => $course->description ?? '',
                    'thumbnail' => $course->image
                        ? (file_exists(getFilePath('course') . '/' . $course->image)
                            ? getImage(getFilePath('course') . '/' . $course->image, getFileThumb('course'))
                            : asset('assets/images/course/' . $course->image))
                        : 'https://picsum.photos/400/250?random=' . $course->id,
                    'duration' => $course->duration ?? '0 hours',
                    'students_count' => $course->students_count ?? 0,
                    'price' => (float) $course->price,
                    'is_free' => ((float) $course->price) <= 0,
                    'required_course_plan_id' => (int) ($course->required_course_plan_id ?? 1),
                    'category' => $course->category ?? 'General',
                ];
            })
            ->values()
            ->toArray();

        return responseSuccess('courses', ['Courses retrieved successfully'], [
            'data' => $allCourses,
            'list' => $allCourses,
            'courses' => $allCourses,
            'courses_list' => $allCourses,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }

    /**
     * Mark course as complete and issue certificate.
     */
    public function markComplete(Request $request)
    {
        $request->validate(['course_id' => 'required|integer']);

        $user = auth()->user();
        if (!$this->hasAdCertificate($user)) {
            return responseError('ad_certificate_required', ['Please purchase Ad Certificate (₹1250) to unlock courses and certificates.']);
        }
        $courseId = (int) $request->course_id;
        $planIdToLevel = CoursePlan::query()->pluck('level', 'id')->map(fn ($v) => (int) $v)->toArray();

        $course = Course::active()->find($courseId);
        if (!$course) {
            return responseError('course_not_found', ['Course not found']);
        }

        // Check user has access (active course plan must unlock this course)
        $order = CoursePlanOrder::where('user_id', $user->id)
            ->active()
            ->with('plan')
            ->orderByDesc('course_plan_id')
            ->first();

        if (!$order || !$order->plan) {
            $order = CoursePlanOrder::where('user_id', $user->id)
                ->with('plan')
                ->orderByDesc('id')
                ->first();
        }

        $userPlanLevel = $order && $order->plan ? (int) $order->plan->level : 0;
        $planIsActive = (bool) ($order && $order->status == 1 && (!$order->expires_at || $order->expires_at->isFuture()));
        $effectivePlanLevel = $planIsActive ? $userPlanLevel : 0;

        $requiredCoursePlanId = (int) ($course->required_course_plan_id ?? 1);
        $requiredLevel = $this->requiredLevel($requiredCoursePlanId, $planIdToLevel);
        $hasAccess = $effectivePlanLevel >= $requiredLevel;
        if (!$hasAccess) {
            return responseError('no_access', ['Your course plan is not active or does not unlock this course. Please purchase/renew a course package.']);
        }

        if (CourseCompletion::where('user_id', $user->id)->where('course_id', $courseId)->exists()) {
            return responseSuccess('already_completed', ['Course already completed'], [
                'certificate_number' => UserCertificate::where('user_id', $user->id)->where('course_id', $courseId)->value('certificate_number'),
            ]);
        }

        CourseCompletion::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'completed_at' => now(),
        ]);

        $certNumber = 'CERT-' . strtoupper(uniqid()) . '-' . $user->id;
        UserCertificate::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'certificate_number' => $certNumber,
            'issued_at' => now(),
        ]);

        return responseSuccess('course_completed', ['Course completed! Your certificate has been issued.'], [
            'certificate_number' => $certNumber,
            'course_title' => $course->name,
        ]);
    }
}
