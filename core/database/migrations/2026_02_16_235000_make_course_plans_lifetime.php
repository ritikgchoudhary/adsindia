<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make learning/course plan packages lifetime:
        // - course_plans.validity_days => 0
        // - course_plan_orders.expires_at => NULL
        if (Schema::hasTable('course_plans') && Schema::hasColumn('course_plans', 'validity_days')) {
            DB::table('course_plans')->update(['validity_days' => 0]);
        }

        if (Schema::hasTable('course_plan_orders') && Schema::hasColumn('course_plan_orders', 'expires_at')) {
            DB::table('course_plan_orders')->update(['expires_at' => null]);
        }
    }

    public function down(): void
    {
        // Non-reversible safely (we don't know previous validity/expires_at values).
    }
};

