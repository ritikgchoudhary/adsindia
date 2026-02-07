<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function getCourses()
    {
        $general = gs();

        // Video courses as per requirements
        $courses = [
            [
                'id' => 1,
                'title' => 'Video Editing Masterclass',
                'description' => 'Learn professional video editing techniques with hands-on projects',
                'thumbnail' => '/assets/images/course1.jpg',
                'duration' => '10 hours',
                'students_count' => 1250,
                'price' => 999,
                'is_free' => false,
                'video_url' => '/assets/videos/video-editing-course.mp4',
            ],
            [
                'id' => 2,
                'title' => 'Graphic Designing Basics',
                'description' => 'Master the fundamentals of graphic design and create stunning visuals',
                'thumbnail' => '/assets/images/course2.jpg',
                'duration' => '8 hours',
                'students_count' => 890,
                'price' => 799,
                'is_free' => false,
                'video_url' => '/assets/videos/graphic-design-course.mp4',
            ],
            [
                'id' => 3,
                'title' => 'Digital Marketing Fundamentals',
                'description' => 'Learn digital marketing strategies and grow your business online',
                'thumbnail' => '/assets/images/course3.jpg',
                'duration' => '12 hours',
                'students_count' => 2100,
                'price' => 0,
                'is_free' => true,
                'video_url' => '/assets/videos/digital-marketing-course.mp4',
            ],
            [
                'id' => 4,
                'title' => 'Web Development Course',
                'description' => 'Build modern websites and web applications from scratch',
                'thumbnail' => '/assets/images/course4.jpg',
                'duration' => '15 hours',
                'students_count' => 1500,
                'price' => 1299,
                'is_free' => false,
                'video_url' => '/assets/videos/web-development-course.mp4',
            ],
            [
                'id' => 5,
                'title' => 'Content Writing Mastery',
                'description' => 'Learn to write engaging content that converts',
                'thumbnail' => '/assets/images/course5.jpg',
                'duration' => '6 hours',
                'students_count' => 800,
                'price' => 599,
                'is_free' => false,
                'video_url' => '/assets/videos/content-writing-course.mp4',
            ],
        ];

        return responseSuccess('courses', ['Courses retrieved successfully'], [
            'data' => $courses,
            'currency_symbol' => $general->cur_sym ?? '₹',
        ]);
    }
}
