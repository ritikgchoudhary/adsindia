<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_plans')) {
            Schema::create('course_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique()->nullable();
                $table->decimal('price', 28, 8)->default(0);
                $table->text('description')->nullable();
                $table->unsignedTinyInteger('level')->default(1)->comment('1=Basic, 2=Pro, 3=Premium - higher unlocks more courses');
                $table->integer('validity_days')->default(365);
                $table->integer('status')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('course_plan_orders')) {
            Schema::create('course_plan_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('course_plan_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 28, 8)->default(0);
                $table->integer('status')->default(1);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('course_plan_orders');
        Schema::dropIfExists('course_plans');
    }
};
