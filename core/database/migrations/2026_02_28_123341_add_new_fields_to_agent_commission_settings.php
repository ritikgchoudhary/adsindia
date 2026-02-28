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
        Schema::table('agent_commission_settings', function (Blueprint $table) {
            $table->boolean('kyc_fast_track_enabled')->default(true);
            $table->string('kyc_fast_track_mode')->default('percent')->comment('percent, fixed');
            $table->decimal('kyc_fast_track_value', 28, 8)->default(50);

            $table->boolean('instant_payout_enabled')->default(true);
            $table->string('instant_payout_mode')->default('percent')->comment('percent, fixed');
            $table->decimal('instant_payout_value', 28, 8)->default(50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_commission_settings', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_fast_track_enabled', 'kyc_fast_track_mode', 'kyc_fast_track_value',
                'instant_payout_enabled', 'instant_payout_mode', 'instant_payout_value'
            ]);
        });
    }
};
