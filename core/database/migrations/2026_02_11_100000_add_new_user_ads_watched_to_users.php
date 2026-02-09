<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * New user offer: 2 ads = 10K. This column tracks how many of those 2 ads the user has watched.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'new_user_ads_watched')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('new_user_ads_watched')->default(0)->after('balance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'new_user_ads_watched')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('new_user_ads_watched');
            });
        }
    }
};
