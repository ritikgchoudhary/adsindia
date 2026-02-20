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
                'name' => 'AdsLite',
                'slug' => 'learn-lite',
                'price' => 1499,
                'description' => 'Start learning with essential video courses.',
                'level' => 1,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'AdsPro',
                'slug' => 'learn-pro',
                'price' => 2999,
                'description' => 'Unlock more courses and level up your skills.',
                'level' => 2,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'AdsSupreme',
                'slug' => 'learn-supreme',
                'price' => 5999,
                'description' => 'Supreme access to advanced learning courses.',
                'level' => 3,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'AdsPremium',
                'slug' => 'learn-premium',
                'price' => 9999,
                'description' => 'Premium access to more courses and certificates.',
                'level' => 4,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'AdsPremium+',
                'slug' => 'learn-premium-plus',
                'price' => 15999,
                'description' => 'All-access package for the ultimate learning experience.',
                'level' => 5,
                'validity_days' => 365,
                'status' => 1,
                'sort_order' => 5,
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
