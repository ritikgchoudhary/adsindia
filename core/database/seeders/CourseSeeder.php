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
        // Deactivate older demo/test rows so package counts match (Lite=4, Pro=6, Supreme=9, Premium=12, Premium+=15)
        $oldSlugs = [
            'test-package-1',
            'video-editing-masterclass',
            'graphics-designing-course',
            'digital-marketing-fundamentals',
            'content-writing-copywriting',
            'ui-ux-design-beginners',
            'web-development-html-css-js',
        ];
        Course::whereIn('slug', $oldSlugs)->update(['status' => 0]);

        // Official catalogue: 15 courses mapped to plan levels
        $demos = [
            // Lite (4)
            [
                'name' => 'Affiliate Marketing Mastery',
                'slug' => 'affiliate-marketing-mastery',
                'description' => 'Start earning with affiliate marketing. Learn niche selection, tracking, offers, and conversion strategies.',
                'category' => 'Digital Marketing',
                'duration' => '6 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'students_count' => 1100,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Social Media Optimization',
                'slug' => 'social-media-optimization',
                'description' => 'Optimize your profiles and content for organic growth. Hashtags, reels strategy, and engagement.',
                'category' => 'Digital Marketing',
                'duration' => '5 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'students_count' => 980,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Canva Designing',
                'slug' => 'canva-designing',
                'description' => 'Create attractive posters, thumbnails and social creatives using Canva. Templates, brand kit, exports.',
                'category' => 'Graphic Design',
                'duration' => '4 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'students_count' => 1250,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Communication Skills',
                'slug' => 'communication-skills',
                'description' => 'Improve spoken communication, confidence, and interview-ready skills with practical exercises.',
                'category' => 'Communication',
                'duration' => '5 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'students_count' => 900,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 1,
                'sort_order' => 4,
            ],

            // Pro (adds 2 => total 6)
            [
                'name' => 'Facebook Ads Mastery',
                'slug' => 'facebook-ads-mastery',
                'description' => 'Run profitable Facebook ad campaigns. Pixel setup, audiences, creatives, and scaling.',
                'category' => 'Digital Marketing',
                'duration' => '7 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
                'students_count' => 1450,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 2,
                'sort_order' => 5,
            ],
            [
                'name' => 'Instagram Growth Secrets',
                'slug' => 'instagram-growth-secrets',
                'description' => 'Grow your Instagram with reels, content calendar, hooks, and analytics-based optimization.',
                'category' => 'Digital Marketing',
                'duration' => '6 hours',
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'students_count' => 1320,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 2,
                'sort_order' => 6,
            ],

            // Supreme (adds 3 => total 9)
            [
                'name' => 'Google Ads Mastery',
                'slug' => 'google-ads-mastery',
                'description' => 'Search ads, keywords, match types, ad copies, conversion tracking and ROI optimization.',
                'category' => 'Digital Marketing',
                'duration' => '8 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'students_count' => 990,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 3,
                'sort_order' => 7,
            ],
            [
                'name' => 'YouTube Domination',
                'slug' => 'youtube-domination',
                'description' => 'Build a YouTube channel that grows. SEO, thumbnails, retention, and monetization basics.',
                'category' => 'Content Creation',
                'duration' => '7 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                'students_count' => 880,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 3,
                'sort_order' => 8,
            ],
            [
                'name' => 'Video Editing Mastery',
                'slug' => 'video-editing-mastery',
                'description' => 'Advanced video editing workflow: timelines, transitions, sound, color grading and exports.',
                'category' => 'Video Editing',
                'duration' => '10 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/VolkswagenGTIReview.mp4',
                'students_count' => 740,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 3,
                'sort_order' => 9,
            ],

            // Premium (adds 3 => total 12)
            [
                'name' => 'Stock Market Basics',
                'slug' => 'stock-market-basics',
                'description' => 'Understand stock market fundamentals, risk management, and long-term investing basics.',
                'category' => 'Finance',
                'duration' => '6 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
                'students_count' => 630,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 4,
                'sort_order' => 10,
            ],
            [
                'name' => 'Crypto Currency Trading',
                'slug' => 'crypto-currency-trading',
                'description' => 'Crypto fundamentals, exchanges, risk, charts, and beginner-friendly trading strategies.',
                'category' => 'Finance',
                'duration' => '6 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
                'students_count' => 520,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 4,
                'sort_order' => 11,
            ],
            [
                'name' => 'Public Speaking Mastery',
                'slug' => 'public-speaking-mastery',
                'description' => 'Stage confidence, speech structuring, voice modulation and presentation skills.',
                'category' => 'Communication',
                'duration' => '5 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4',
                'students_count' => 410,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 4,
                'sort_order' => 12,
            ],

            // Premium+ (adds 3 => total 15)
            [
                'name' => 'Advanced Digital Marketing',
                'slug' => 'advanced-digital-marketing',
                'description' => 'Advanced funnels, retargeting, email automation and analytics to scale campaigns.',
                'category' => 'Digital Marketing',
                'duration' => '9 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/WeAreGoingOnBullrun.mp4',
                'students_count' => 360,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 5,
                'sort_order' => 13,
            ],
            [
                'name' => 'Website Development',
                'slug' => 'website-development',
                'description' => 'Build and deploy websites. Domain, hosting, WordPress basics, and essential web concepts.',
                'category' => 'Web Development',
                'duration' => '10 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
                'students_count' => 420,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 5,
                'sort_order' => 14,
            ],
            [
                'name' => 'Freelancing Mastery',
                'slug' => 'freelancing-mastery',
                'description' => 'Start freelancing with proven methods: profiles, proposals, pricing, delivery and client retention.',
                'category' => 'Freelancing',
                'duration' => '7 hours',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'students_count' => 500,
                'price' => 0,
                'required_package_id' => 1,
                'required_course_plan_id' => 5,
                'sort_order' => 15,
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
