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
            if (!Schema::hasColumn('general_settings', 'ads_enabled')) {
                $table->tinyInteger('ads_enabled')->default(1)->after('system_affiliate_commission');
            }
            if (!Schema::hasColumn('general_settings', 'ads_reward_mode')) {
                $table->string('ads_reward_mode', 20)->default('random')->after('ads_enabled'); // random|fixed
            }
            if (!Schema::hasColumn('general_settings', 'ads_reward_fixed')) {
                $table->decimal('ads_reward_fixed', 28, 8)->default(0)->after('ads_reward_mode');
            }
            if (!Schema::hasColumn('general_settings', 'ads_reward_min')) {
                $table->decimal('ads_reward_min', 28, 8)->default(1000)->after('ads_reward_fixed');
            }
            if (!Schema::hasColumn('general_settings', 'ads_reward_max')) {
                $table->decimal('ads_reward_max', 28, 8)->default(5000)->after('ads_reward_min');
            }
            if (!Schema::hasColumn('general_settings', 'ads_reward_step')) {
                $table->decimal('ads_reward_step', 28, 8)->default(100)->after('ads_reward_max');
            }
            if (!Schema::hasColumn('general_settings', 'ads_reward_multiplier')) {
                $table->decimal('ads_reward_multiplier', 28, 8)->default(1)->after('ads_reward_step');
            }

            // New user offer controls
            if (!Schema::hasColumn('general_settings', 'new_user_offer_enabled')) {
                $table->tinyInteger('new_user_offer_enabled')->default(1)->after('ads_reward_multiplier');
            }
            if (!Schema::hasColumn('general_settings', 'new_user_offer_ads')) {
                $table->integer('new_user_offer_ads')->default(2)->after('new_user_offer_enabled');
            }
            if (!Schema::hasColumn('general_settings', 'new_user_offer_reward')) {
                $table->decimal('new_user_offer_reward', 28, 8)->default(5000)->after('new_user_offer_ads');
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

