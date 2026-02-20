<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users: separate affiliate wallet balance
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'affiliate_balance')) {
            Schema::table('users', function (Blueprint $table) {
                // Match existing balance precision
                $table->decimal('affiliate_balance', 28, 8)->default(0)->after('balance');
            });
        }

        // Transactions: tag which wallet a transaction belongs to
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'wallet')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('wallet', 20)->default('main')->after('remark');
            });
        }

        // Withdrawals: tag which wallet the withdrawal is from
        if (Schema::hasTable('withdrawals') && !Schema::hasColumn('withdrawals', 'wallet')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->string('wallet', 20)->default('main')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('withdrawals') && Schema::hasColumn('withdrawals', 'wallet')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->dropColumn('wallet');
            });
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'wallet')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('wallet');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'affiliate_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('affiliate_balance');
            });
        }
    }
};

