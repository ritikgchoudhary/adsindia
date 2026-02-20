<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Agent tag on users
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_agent')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_agent')->default(false)->after('ref_by');
            });
        }

        // 2) Per-agent commission settings (dynamic; no hardcoding)
        if (!Schema::hasTable('agent_commission_settings')) {
            Schema::create('agent_commission_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

                // Registration commission
                $table->boolean('registration_enabled')->default(false);
                $table->enum('registration_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('registration_value', 28, 8)->default(0);

                // KYC commission
                $table->boolean('kyc_enabled')->default(false);
                $table->enum('kyc_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('kyc_value', 28, 8)->default(0);

                // Withdrawal fee commission (commission on fee amount)
                $table->boolean('withdraw_fee_enabled')->default(false);
                $table->enum('withdraw_fee_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('withdraw_fee_value', 28, 8)->default(0);

                // Upgrade commission (future: package renew/upgrade)
                $table->boolean('upgrade_enabled')->default(false);
                $table->enum('upgrade_mode', ['percent', 'fixed'])->default('percent');
                $table->decimal('upgrade_value', 28, 8)->default(0);

                // Partner override (downline) commission %
                $table->boolean('partner_override_enabled')->default(false);
                $table->decimal('partner_override_percent', 10, 4)->default(0);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agent_commission_settings')) {
            Schema::dropIfExists('agent_commission_settings');
        }
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_agent')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_agent');
            });
        }
    }
};

