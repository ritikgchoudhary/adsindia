<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::ordered()->get()->map(function ($course) {
            $plan = \App\Models\CoursePlan::find($course->required_course_plan_id);
            return [
                'id' => $course->id,
                'name' => $course->name,
                'slug' => $course->slug,
                'description' => $course->description,
                'category' => $course->category,
                'duration' => $course->duration,
                'video_url' => $course->video_url,
                'required_package_id' => $course->required_package_id,
                'required_course_plan_id' => $course->required_course_plan_id ?? 1,
                'package_name' => $plan ? $plan->name : ('Plan #' . ($course->required_course_plan_id ?? 1)),
                'price' => (float) $course->price,
                'students_count' => $course->students_count ?? 0,
                'status' => $course->status,
                'is_recommended' => $course->is_recommended ?? false,
                'sort_order' => $course->sort_order ?? 0,
                'affiliate_commission_percent' => $course->affiliate_commission_percent ?? 0,
                'affiliate_commission_fixed' => $course->affiliate_commission_fixed ?? 0,
                'thumbnail' => $course->image ? getImage(getFilePath('course') . '/' . $course->image, getFileThumb('course')) : null,
            ];
        });

        return responseSuccess('courses', ['Courses retrieved successfully'], $courses);
    }

    public function create()
    {
        // This method is not needed for API, but kept for compatibility
        return responseSuccess('create_form', ['Create form data']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:courses,slug',
            'description' => 'nullable|string',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'video_url' => 'nullable|max:500',
            'duration' => 'nullable|string|max:50',
            'students_count' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'required_package_id' => 'required|integer|min:1|max:5',
            'category' => 'nullable|string|max:100',
            'affiliate_commission_percent' => 'nullable|numeric|min:0|max:100',
            'affiliate_commission_fixed' => 'nullable|numeric|min:0',
            'is_recommended' => 'nullable|boolean',
            'status' => 'required|integer|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $course = new Course();
        $course->name = $request->name;
        $course->slug = $request->slug ?? \Str::slug($request->name);
        $course->description = $request->description;
        $course->video_url = $request->video_url;
        $course->duration = $request->duration;
        $course->students_count = $request->students_count ?? 0;
        $course->price = $request->price;
        $course->required_package_id = $request->required_package_id;
        $course->category = $request->category;
        $course->affiliate_commission_percent = $request->affiliate_commission_percent ?? 0;
        $course->affiliate_commission_fixed = $request->affiliate_commission_fixed ?? 0;
        $course->is_recommended = $request->is_recommended ?? false;
        $course->status = $request->status;
        $course->sort_order = $request->sort_order ?? 0;

        if ($request->hasFile('image')) {
            $course->image = fileUploader(
                $request->image,
                getFilePath('course'),
                getFileSize('course'),
                null,
                getFileThumb('course')
            );
        }

        $course->save();

        // Check if API request
        if (request()->expectsJson() || request()->is('api/*')) {
            return responseSuccess('course_created', ['Course created successfully'], [
                'id' => $course->id,
                'name' => $course->name,
            ]);
        }

        $notify[] = ['success', 'Course created successfully'];
        return back()->withNotify($notify);
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        
        $courseData = [
            'id' => $course->id,
            'name' => $course->name,
            'slug' => $course->slug,
            'description' => $course->description,
            'category' => $course->category,
            'duration' => $course->duration,
            'video_url' => $course->video_url,
            'required_package_id' => $course->required_package_id,
            'required_course_plan_id' => $course->required_course_plan_id ?? 1,
            'price' => (float) $course->price,
            'students_count' => $course->students_count ?? 0,
            'status' => $course->status,
            'is_recommended' => $course->is_recommended ?? false,
            'sort_order' => $course->sort_order ?? 0,
            'affiliate_commission_percent' => $course->affiliate_commission_percent ?? 0,
            'affiliate_commission_fixed' => $course->affiliate_commission_fixed ?? 0,
            'image' => $course->image,
            'image_url' => $course->image ? getImage(getFilePath('course') . '/' . $course->image, getFileThumb('course')) : null,
        ];

        return responseSuccess('course', ['Course retrieved successfully'], $courseData);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:courses,slug,' . $id,
            'description' => 'nullable|string',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'video_url' => 'nullable|max:500',
            'duration' => 'nullable|string|max:50',
            'students_count' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'required_package_id' => 'required|integer|min:1|max:5',
            'required_course_plan_id' => 'nullable|integer|exists:course_plans,id',
            'category' => 'nullable|string|max:100',
            'affiliate_commission_percent' => 'nullable|numeric|min:0|max:100',
            'affiliate_commission_fixed' => 'nullable|numeric|min:0',
            'is_recommended' => 'nullable|boolean',
            'status' => 'required|integer|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $course->name = $request->name;
        $course->slug = $request->slug ?? \Str::slug($request->name);
        $course->description = $request->description;
        $course->video_url = $request->video_url;
        $course->duration = $request->duration;
        $course->students_count = $request->students_count ?? 0;
        $course->price = $request->price;
        $course->required_package_id = $request->required_package_id;
        if ($request->has('required_course_plan_id')) {
            $course->required_course_plan_id = $request->required_course_plan_id ?: 1;
        }
        $course->category = $request->category;
        $course->affiliate_commission_percent = $request->affiliate_commission_percent ?? 0;
        $course->affiliate_commission_fixed = $request->affiliate_commission_fixed ?? 0;
        $course->is_recommended = $request->is_recommended ?? false;
        $course->status = $request->status;
        $course->sort_order = $request->sort_order ?? 0;

        if ($request->hasFile('image')) {
            $course->image = fileUploader(
                $request->image,
                getFilePath('course'),
                getFileSize('course'),
                $course->image,
                getFileThumb('course')
            );
        }

        $course->save();

        // Check if API request
        if (request()->expectsJson() || request()->is('api/*')) {
            return responseSuccess('course_updated', ['Course updated successfully'], [
                'id' => $course->id,
                'name' => $course->name,
            ]);
        }

        $notify[] = ['success', 'Course updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $course = Course::findOrFail($id);
        if ($course->image) {
            fileManager()->removeFile(getFilePath('course') . '/' . $course->image);
            fileManager()->removeFile(getFilePath('course') . '/thumb_' . $course->image);
        }
        $course->delete();

        // Check if API request
        if (request()->expectsJson() || request()->is('api/*')) {
            return responseSuccess('course_deleted', ['Course deleted successfully']);
        }

        $notify[] = ['success', 'Course deleted successfully'];
        return back()->withNotify($notify);
    }
}
