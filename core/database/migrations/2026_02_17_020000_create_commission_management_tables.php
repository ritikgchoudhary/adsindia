<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Direct Affiliate Commission (All users) - package wise fixed amounts (enable/disable)
         */
        if (!Schema::hasTable('direct_affiliate_commissions')) {
            Schema::create('direct_affiliate_commissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('package_id')->unique();
                $table->decimal('package_price', 28, 8)->default(0);
                $table->decimal('commission_amount', 28, 8)->default(0);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        /**
         * Agent commission defaults (Master Admin controlled) - used when enabling new agents.
         * Per-agent overrides remain in agent_commission_settings table.
         */
        if (!Schema::hasTable('agent_commission_defaults')) {
            Schema::create('agent_commission_defaults', function (Blueprint $table) {
                $table->id();

                $table->boolean('registration_enabled')->default(true);
                $table->enum('registration_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('registration_value', 28, 8)->default(50);

                $table->boolean('kyc_enabled')->default(true);
                $table->enum('kyc_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('kyc_value', 28, 8)->default(50);

                $table->boolean('withdraw_fee_enabled')->default(true);
                $table->enum('withdraw_fee_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('withdraw_fee_value', 28, 8)->default(50);

                $table->boolean('upgrade_enabled')->default(true);
                $table->enum('upgrade_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('upgrade_value', 28, 8)->default(50);

                $table->timestamps();
            });
        }

        /**
         * Agent upgrade commission rules (Master Admin controlled) - per plan override.
         * Can be used for ad plans, ad package upgrades, course plans, partner plans, etc.
         */
        if (!Schema::hasTable('agent_upgrade_commission_rules')) {
            Schema::create('agent_upgrade_commission_rules', function (Blueprint $table) {
                $table->id();
                $table->string('plan_type', 30); // ad_plan|package|course_plan|partner_plan (extensible)
                $table->unsignedBigInteger('plan_id');
                $table->boolean('enabled')->default(true);
                $table->enum('mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('value', 28, 8)->default(0);
                $table->timestamps();

                $table->unique(['plan_type', 'plan_id'], 'agent_upgrade_rules_type_id_unique');
                $table->index(['plan_type'], 'agent_upgrade_rules_plan_type_idx');
            });
        }

        // Seed defaults (safe/idempotent)
        try {
            if (Schema::hasTable('agent_commission_defaults')) {
                $exists = DB::table('agent_commission_defaults')->exists();
                if (!$exists) {
                    DB::table('agent_commission_defaults')->insert([
                        'registration_enabled' => true,
                        'registration_mode' => 'percent',
                        'registration_value' => 50,
                        'kyc_enabled' => true,
                        'kyc_mode' => 'percent',
                        'kyc_value' => 50,
                        'withdraw_fee_enabled' => true,
                        'withdraw_fee_mode' => 'percent',
                        'withdraw_fee_value' => 50,
                        'upgrade_enabled' => true,
                        'upgrade_mode' => 'percent',
                        'upgrade_value' => 50,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // no-op
        }

        try {
            if (Schema::hasTable('direct_affiliate_commissions')) {
                $hasAny = DB::table('direct_affiliate_commissions')->exists();
                if (!$hasAny) {
                    DB::table('direct_affiliate_commissions')->insert([
                        [
                            'package_id' => 1,
                            'package_price' => 1499,
                            'commission_amount' => 750,
                            'enabled' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'package_id' => 2,
                            'package_price' => 2999,
                            'commission_amount' => 1500,
                            'enabled' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'package_id' => 3,
                            'package_price' => 5999,
                            'commission_amount' => 3000,
                            'enabled' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'package_id' => 4,
                            'package_price' => 9999,
                            'commission_amount' => 5000,
                            'enabled' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'package_id' => 5,
                            'package_price' => 15999,
                            'commission_amount' => 8000,
                            'enabled' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // no-op
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agent_upgrade_commission_rules')) {
            Schema::dropIfExists('agent_upgrade_commission_rules');
        }
        if (Schema::hasTable('agent_commission_defaults')) {
            Schema::dropIfExists('agent_commission_defaults');
        }
        if (Schema::hasTable('direct_affiliate_commissions')) {
            Schema::dropIfExists('direct_affiliate_commissions');
        }
    }
};

