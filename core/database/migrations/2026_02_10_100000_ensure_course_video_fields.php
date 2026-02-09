<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure courses table has video/category fields (if missing after create_courses_table).
     */
    public function up(): void
    {
        if (!Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'video_url')) {
                $table->string('video_url')->nullable()->after('image');
            }
            if (!Schema::hasColumn('courses', 'duration')) {
                $table->string('duration')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('courses', 'students_count')) {
                $table->integer('students_count')->default(0)->after('duration');
            }
            if (!Schema::hasColumn('courses', 'required_package_id')) {
                $table->integer('required_package_id')->default(1)->after('students_count');
            }
            if (!Schema::hasColumn('courses', 'category')) {
                $table->string('category')->nullable()->after('required_package_id');
            }
        });
    }

    public function down(): void
    {
        // Optional: drop columns if you want to rollback
    }
};
