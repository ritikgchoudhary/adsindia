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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->timestamp('admin_orders_cleared_at')->nullable();
            $table->timestamp('admin_deposits_cleared_at')->nullable();
            $table->timestamp('admin_withdrawals_cleared_at')->nullable();
            $table->timestamp('admin_transactions_cleared_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'admin_orders_cleared_at',
                'admin_deposits_cleared_at',
                'admin_withdrawals_cleared_at',
                'admin_transactions_cleared_at'
            ]);
        });
    }
};
