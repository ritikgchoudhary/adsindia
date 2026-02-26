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
        Schema::table('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->comment('Link to users table for downline filtering');
            $table->json('permissions')->nullable()->after('password');
            $table->boolean('is_super_admin')->default(false)->after('status');
        });

        // Set ID 1 as Super Admin by default
        \DB::table('admins')->where('id', 1)->update(['is_super_admin' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'permissions', 'is_super_admin']);
        });
    }
};
