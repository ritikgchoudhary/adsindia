<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'has_paid_kyc_fee')) {
                $table->boolean('has_paid_kyc_fee')->default(false);
            }
            if (!Schema::hasColumn('users', 'kyc_fee_trx')) {
                $table->string('kyc_fee_trx', 191)->nullable();
            }
            if (!Schema::hasColumn('users', 'kyc_fee_paid_at')) {
                $table->timestamp('kyc_fee_paid_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'has_paid_kyc_fee')) {
                $table->dropColumn('has_paid_kyc_fee');
            }
            if (Schema::hasColumn('users', 'kyc_fee_trx')) {
                $table->dropColumn('kyc_fee_trx');
            }
            if (Schema::hasColumn('users', 'kyc_fee_paid_at')) {
                $table->dropColumn('kyc_fee_paid_at');
            }
        });
    }
};

