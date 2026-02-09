<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('image');
            $table->string('duration')->nullable()->after('video_url');
            $table->integer('students_count')->default(0)->after('duration');
            $table->integer('required_package_id')->default(1)->after('students_count')->comment('Package ID required to access this course (1=AdsLite, 2=AdsPro, 3=AdsSupreme, 4=AdsPremium, 5=AdsPremium+)');
            $table->string('category')->nullable()->after('required_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'duration', 'students_count', 'required_package_id', 'category']);
        });
    }
};
