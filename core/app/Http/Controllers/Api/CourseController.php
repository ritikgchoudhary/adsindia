<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\CoursePlanOrder;
use App\Models\UserCertificate;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Get courses unlocked by user's course plan. User must buy a course plan to access courses.
     */
    public function getCourses()
    {
        $user = auth()->user();
        $general = gs();

        // User's active course plan (highest level)
        $order = CoursePlanOrder::where('user_id', $user->id)
            ->active()
            ->with('plan')
            ->orderByDesc('course_plan_id')
            ->first();

        $userPlanLevel = $order ? (int) $order->plan->level : 0;

        $allCourses = Course::active()
            ->ordered()
            ->get()
            ->map(function ($course) use ($user) {
                $requiredPlan = (int) ($course->required_course_plan_id ?? 1);
                $completed = CourseCompletion::where('user_id', $user->id)->where('course_id', $course->id)->exists();
                return [
                    'id' => $course->id,
                    'title' => $course->name,
                    'description' => $course->description ?? '',
                    'thumbnail' => $course->image ? (file_exists(getFilePath('course') . '/' . $course->image) ? getImage(getFilePath('course') . '/' . $course->image, getFileThumb('course')) : asset('assets/images/course/' . $course->image)) : 'https://picsum.photos/400/250?random=' . $course->id,
                    'duration' => $course->duration ?? '0 hours',
                    'students_count' => $course->students_count ?? 0,
                    'price' => (float) $course->price,
                    'is_free' => (float) $course->price == 0,
                    'video_url' => $course->video_url ?? '',
                    'required_package' => $course->required_package_id ?? 1,
                    'required_course_plan_id' => $requiredPlan,
                    'category' => $course->category ?? 'General',
                    'is_completed' => $completed,
                ];
            })
            ->toArray();

        // Filter by course plan: user sees courses where required_course_plan_id <= user's plan level
        $availableCourses = [];
        foreach ($allCourses as $course) {
            if ($userPlanLevel >= ($course['required_course_plan_id'] ?? 1)) {
                $availableCourses[] = $course;
            }
        }
        $availableCourses = array_values($availableCourses);

        $payload = [
            'data' => $availableCourses,
            'list' => $availableCourses,
            'courses' => $availableCourses,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'user_course_plan_level' => $userPlanLevel,
            'total_courses' => count($availableCourses),
            'has_course_plan' => $userPlanLevel > 0,
            'current_plan_name' => $order ? $order->plan->name : null,
        ];
        $response = response()->json([
            'remark' => 'courses',
            'status' => 'success',
            'message' => ['success' => ['Courses retrieved successfully']],
            'data' => $payload,
            'courses_list' => $availableCourses,
        ]);
        return $response;
    }

    /**
     * Mark course as complete and issue certificate.
     */
    public function markComplete(Request $request)
    {
        $request->validate(['course_id' => 'required|integer']);

        $user = auth()->user();
        $courseId = (int) $request->course_id;

        // Check user has access (has course plan that unlocks this course)
        $order = CoursePlanOrder::where('user_id', $user->id)->active()->with('plan')->orderByDesc('course_plan_id')->first();
        $userPlanLevel = $order ? (int) $order->plan->level : 0;

        $course = Course::active()->find($courseId);
        if (!$course) {
            return responseError('course_not_found', ['Course not found']);
        }

        $requiredPlan = (int) ($course->required_course_plan_id ?? 1);
        if ($userPlanLevel < $requiredPlan) {
            return responseError('no_access', ['Purchase a course plan to access this course']);
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
