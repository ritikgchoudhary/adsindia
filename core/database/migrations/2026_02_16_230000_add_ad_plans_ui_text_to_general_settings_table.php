<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('general_settings')) {
            return;
        }

        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'ad_plans_info_title')) {
                $table->string('ad_plans_info_title', 255)->nullable()->after('new_user_offer_reward');
            }
            if (!Schema::hasColumn('general_settings', 'ad_plans_info_description')) {
                $table->text('ad_plans_info_description')->nullable()->after('ad_plans_info_title');
            }
            if (!Schema::hasColumn('general_settings', 'ad_plans_info_bullets')) {
                // JSON array of strings
                $table->text('ad_plans_info_bullets')->nullable()->after('ad_plans_info_description');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive rollback (safe on prod)
        if (!Schema::hasTable('general_settings')) {
            return;
        }
    }
};

