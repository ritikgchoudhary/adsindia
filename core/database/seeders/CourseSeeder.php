<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Demo video courses for /user/courses (Video Editing, Graphics Design, etc.)
     * Admin can add more from master panel with video link upload.
     */
    public function run(): void
    {
        $demos = [
            [
                'name' => 'Video Editing Masterclass',
                'slug' => 'video-editing-masterclass',
                'description' => 'Learn professional video editing from basics to advanced. Cuts, transitions, color grading, and export for YouTube & social media.',
                'category' => 'Video Editing',
                'duration' => '12 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'students_count' => 1250,
                'price' => 0,
                'required_package_id' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Graphics Designing with Canva & Photoshop',
                'slug' => 'graphics-designing-course',
                'description' => 'Create posters, logos, social media graphics and thumbnails. Learn Canva and Adobe Photoshop basics.',
                'category' => 'Graphic Design',
                'duration' => '8 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'students_count' => 980,
                'price' => 0,
                'required_package_id' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Digital Marketing Fundamentals',
                'slug' => 'digital-marketing-fundamentals',
                'description' => 'SEO, Google Ads, Facebook & Instagram ads, email marketing and analytics for beginners.',
                'category' => 'Digital Marketing',
                'duration' => '10 hours',
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'students_count' => 2100,
                'price' => 0,
                'required_package_id' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Content Writing & Copywriting',
                'slug' => 'content-writing-copywriting',
                'description' => 'Write engaging blog posts, product descriptions and ad copy that converts. Grammar and style tips.',
                'category' => 'Content Writing',
                'duration' => '6 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'students_count' => 756,
                'price' => 0,
                'required_package_id' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'UI/UX Design for Beginners',
                'slug' => 'ui-ux-design-beginners',
                'description' => 'User interface and user experience design basics. Wireframes, prototypes and Figma overview.',
                'category' => 'UI/UX Design',
                'duration' => '9 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'students_count' => 890,
                'price' => 0,
                'required_package_id' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => 'Web Development - HTML, CSS & JavaScript',
                'slug' => 'web-development-html-css-js',
                'description' => 'Build responsive websites with HTML5, CSS3 and JavaScript. Simple projects and deployment.',
                'category' => 'Web Development',
                'duration' => '15 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'students_count' => 1650,
                'price' => 0,
                'required_package_id' => 1,
                'sort_order' => 6,
            ],
        ];

        foreach ($demos as $row) {
            Course::updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'image' => null,
                    'affiliate_commission_percent' => 0,
                    'affiliate_commission_fixed' => 0,
                    'is_recommended' => false,
                    'status' => 1,
                ])
            );
        }
    }
}
