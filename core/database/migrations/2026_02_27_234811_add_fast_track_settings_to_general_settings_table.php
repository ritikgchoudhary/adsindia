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
            if (!Schema::hasColumn('general_settings', 'kyc_fast_track_fee')) {
                $table->decimal('kyc_fast_track_fee', 28, 8)->default(299);
            }
            if (!Schema::hasColumn('general_settings', 'instant_payout_fee')) {
                $table->decimal('instant_payout_fee', 28, 8)->default(50);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'kyc_fast_track_fee')) {
                $table->dropColumn('kyc_fast_track_fee');
            }
            if (Schema::hasColumn('general_settings', 'instant_payout_fee')) {
                $table->dropColumn('instant_payout_fee');
            }
        });
    }
};
