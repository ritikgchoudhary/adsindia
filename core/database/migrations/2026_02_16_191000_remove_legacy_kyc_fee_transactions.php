<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        // Legacy behavior created a debit transaction with remark 'kyc_fee', which looked like a wallet deduction.
        // KYC fee is now gateway-only, tracked on users table fields.
        DB::table('transactions')->where('remark', 'kyc_fee')->delete();
    }

    public function down(): void
    {
        // No-op: we cannot restore deleted legacy rows safely.
    }
};

