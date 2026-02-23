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
        Schema::table('agent_commission_settings', function (Blueprint $blueprint) {
            $blueprint->boolean('ad_certificate_enabled')->default(true);
            $blueprint->string('ad_certificate_mode')->default('percent')->comment('percent or fixed');
            $blueprint->decimal('ad_certificate_value', 28, 8)->default(50);

            $blueprint->boolean('partner_program_enabled')->default(true);
            $blueprint->string('partner_program_mode')->default('percent')->comment('percent or fixed');
            $blueprint->decimal('partner_program_value', 28, 8)->default(50);
        });

        Schema::table('agent_commission_defaults', function (Blueprint $blueprint) {
            $blueprint->boolean('ad_certificate_enabled')->default(true);
            $blueprint->string('ad_certificate_mode')->default('percent');
            $blueprint->decimal('ad_certificate_value', 28, 8)->default(50);

            $blueprint->boolean('partner_program_enabled')->default(true);
            $blueprint->string('partner_program_mode')->default('percent');
            $blueprint->decimal('partner_program_value', 28, 8)->default(50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_commission_settings', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'ad_certificate_enabled', 'ad_certificate_mode', 'ad_certificate_value',
                'partner_program_enabled', 'partner_program_mode', 'partner_program_value'
            ]);
        });

        Schema::table('agent_commission_defaults', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'ad_certificate_enabled', 'ad_certificate_mode', 'ad_certificate_value',
                'partner_program_enabled', 'partner_program_mode', 'partner_program_value'
            ]);
        });
    }
};
