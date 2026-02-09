<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdPackageOrder;
use App\Models\Course;

class CourseController extends Controller
{
    public function getCourses()
    {
        $user = auth()->user();
        $general = gs();

        // Get user's current package
        $currentOrder = AdPackageOrder::where('user_id', $user->id)
            ->active()
            ->latest()
            ->first();

        $userPackageId = $currentOrder ? $currentOrder->package_id : 0;

        // Fetch all active courses from database
        $allCourses = Course::active()
            ->ordered()
            ->get()
            ->map(function ($course) {
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
                    'category' => $course->category ?? 'General',
                ];
            })
            ->toArray();

        // Filter courses based on user's package
        // Users with higher packages get access to all lower package courses
        $availableCourses = [];
        foreach ($allCourses as $course) {
            if ($userPackageId >= $course['required_package']) {
                $availableCourses[] = $course;
            }
        }

        // If no package, show only free/basic courses (package 1)
        if ($userPackageId == 0) {
            $availableCourses = array_filter($allCourses, function($course) {
                return $course['required_package'] == 1;
            });
            $availableCourses = array_values($availableCourses);
        }

        return responseSuccess('courses', ['Courses retrieved successfully'], [
            'data' => $availableCourses,
            'currency_symbol' => $general->cur_sym ?? '₹',
            'user_package_id' => $userPackageId,
            'total_courses' => count($availableCourses),
        ]);
    }
}
