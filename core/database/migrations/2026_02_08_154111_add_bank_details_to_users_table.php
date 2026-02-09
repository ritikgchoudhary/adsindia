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
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_holder_name')->nullable()->after('address');
            $table->string('account_number')->nullable()->after('account_holder_name');
            $table->string('ifsc_code', 20)->nullable()->after('account_number');
            $table->string('bank_name')->nullable()->after('ifsc_code');
            $table->string('bank_registered_no')->nullable()->after('bank_name');
            $table->string('branch_name')->nullable()->after('bank_registered_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'account_holder_name',
                'account_number',
                'ifsc_code',
                'bank_name',
                'bank_registered_no',
                'branch_name'
            ]);
        });
    }
};
