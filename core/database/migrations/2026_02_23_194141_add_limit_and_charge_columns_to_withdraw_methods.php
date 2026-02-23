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
        Schema::table('withdraw_methods', function (Blueprint $table) {
            // Affiliate Withdrawal Settings
            $table->decimal('affiliate_min_limit', 28, 8)->default(10000);
            $table->decimal('affiliate_max_limit', 28, 8)->default(1000000);
            $table->decimal('affiliate_fixed_charge', 28, 8)->default(0);
            $table->decimal('affiliate_percent_charge', 5, 2)->default(0);

            // User Bank Withdrawal Settings
            $table->decimal('user_bank_fixed_charge', 28, 8)->default(0);
            $table->decimal('user_bank_percent_charge', 5, 2)->default(0);

            // User UPI Withdrawal Settings
            $table->decimal('user_upi_fixed_charge', 28, 8)->default(0);
            $table->decimal('user_upi_percent_charge', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_methods', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_min_limit', 'affiliate_max_limit', 'affiliate_fixed_charge', 'affiliate_percent_charge',
                'user_bank_fixed_charge', 'user_bank_percent_charge',
                'user_upi_fixed_charge', 'user_upi_percent_charge'
            ]);
        });
    }
};
