<?php

namespace Database\Seeders;

use App\Models\CoursePlan;
use Illuminate\Database\Seeder;

class CoursePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Learning',
                'slug' => 'basic-learning',
                'price' => 999,
                'description' => 'Access basic courses. Complete to earn certificates.',
                'level' => 1,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro Learning',
                'slug' => 'pro-learning',
                'price' => 1999,
                'description' => 'Access all Basic + Pro courses. Get certificates on completion.',
                'level' => 2,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium Learning',
                'slug' => 'premium-learning',
                'price' => 3999,
                'description' => 'Access all courses. Complete any course to get certificate.',
                'level' => 3,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $p) {
            CoursePlan::updateOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }
    }
}
