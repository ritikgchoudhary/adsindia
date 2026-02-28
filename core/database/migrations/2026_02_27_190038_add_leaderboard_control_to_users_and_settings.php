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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('lead_ads_today', 28, 8)->default(0);
            $table->decimal('lead_ads_weekly', 28, 8)->default(0);
            $table->decimal('lead_ads_monthly', 28, 8)->default(0);
            $table->decimal('lead_ads_all_time', 28, 8)->default(0);
            
            $table->decimal('lead_aff_today', 28, 8)->default(0);
            $table->decimal('lead_aff_weekly', 28, 8)->default(0);
            $table->decimal('lead_aff_monthly', 28, 8)->default(0);
            $table->decimal('lead_aff_all_time', 28, 8)->default(0);
            
            $table->boolean('is_lb_hidden')->default(false);
        });

        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('lb_show_today')->default(true);
            $table->boolean('lb_show_weekly')->default(true);
            $table->boolean('lb_show_monthly')->default(true);
            $table->boolean('lb_show_all_time')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'lead_ads_today', 'lead_ads_weekly', 'lead_ads_monthly', 'lead_ads_all_time',
                'lead_aff_today', 'lead_aff_weekly', 'lead_aff_monthly', 'lead_aff_all_time',
                'is_lb_hidden'
            ]);
        });

        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['lb_show_today', 'lb_show_weekly', 'lb_show_monthly', 'lb_show_all_time']);
        });
    }
};
