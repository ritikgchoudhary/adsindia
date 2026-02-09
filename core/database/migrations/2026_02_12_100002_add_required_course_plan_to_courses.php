<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('courses') && !Schema::hasColumn('courses', 'required_course_plan_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->unsignedTinyInteger('required_course_plan_id')->default(1)->after('required_package_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('courses', 'required_course_plan_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('required_course_plan_id');
            });
        }
    }
};
