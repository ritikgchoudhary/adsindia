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
            $table->decimal('user_upi_min_limit', 28, 8)->nullable();
            $table->decimal('user_upi_max_limit', 28, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_methods', function (Blueprint $table) {
            $table->dropColumn(['user_upi_min_limit', 'user_upi_max_limit']);
        });
    }
};
